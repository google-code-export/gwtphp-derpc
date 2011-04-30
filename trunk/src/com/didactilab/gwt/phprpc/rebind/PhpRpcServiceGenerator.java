/*
 * Copyright 2011 DidactiLab SAS
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 * 
 * Date: 30 avr. 2011
 * Author: Mathieu LIGOCKI
 */
package com.didactilab.gwt.phprpc.rebind;

import java.io.Serializable;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

import com.didactilab.gwt.phprpc.client.Phpize;
import com.google.gwt.core.ext.BadPropertyValueException;
import com.google.gwt.core.ext.ConfigurationProperty;
import com.google.gwt.core.ext.GeneratorContextExt;
import com.google.gwt.core.ext.TreeLogger;
import com.google.gwt.core.ext.UnableToCompleteException;
import com.google.gwt.core.ext.typeinfo.JClassType;
import com.google.gwt.core.ext.typeinfo.JEnumType;
import com.google.gwt.core.ext.typeinfo.JField;
import com.google.gwt.core.ext.typeinfo.JMethod;
import com.google.gwt.core.ext.typeinfo.JParameterizedType;
import com.google.gwt.core.ext.typeinfo.JType;
import com.google.gwt.core.ext.typeinfo.NotFoundException;
import com.google.gwt.core.ext.typeinfo.TypeOracle;
import com.google.gwt.dev.javac.rebind.RebindResult;
import com.google.gwt.rpc.rebind.RpcServiceGenerator;
import com.google.gwt.user.client.rpc.IsSerializable;
import com.google.gwt.user.rebind.rpc.ProxyCreator;

public class PhpRpcServiceGenerator extends RpcServiceGenerator {

	private JClassType serializableClass;
	private JClassType isSerializableClass;
	
	private HashSet<JClassType> classes = new HashSet<JClassType>();
	private HashSet<JEnumType> enums = new HashSet<JEnumType>();
	private HashSet<JClassType> exceptions = new HashSet<JClassType>();
	
	@Override
	protected ProxyCreator createProxyCreator(JClassType remoteService) {
		return new PhpRpcProxyCreator(remoteService);
	}
	
	private void exploreType(JType type) {
		if (type.isPrimitive() != null)
			return;
		if (type.getQualifiedSourceName().startsWith("java.")) {
			JClassType javaType = type.isClass();
			if (javaType != null) {
				exploreParameterizedType(javaType);
			}
			return;
		}
		
		if (type.isEnum() != null) {
			enums.add(type.isEnum());
		}
		else {
			JClassType classType = type.isClass();
			if (classType != null) {
				if (classes.add(classType)) {
					exploreClass(classType, false);
				}
			}
		}
	}
	
	private void exploreClass(JClassType toExplore, boolean onlyMethods) {
		
		for (JMethod method : toExplore.getMethods()) {
    		JType returnType = method.getReturnType();
    		exploreType(returnType);
    		for (JType type : method.getParameterTypes()) {
    			exploreType(type);
    		}
    	}
		
		explorePhpize(toExplore);
		
		if (onlyMethods)
			return;
		
		JClassType superClass = toExplore.getSuperclass();
		if (superClass != null) {
			exploreType(superClass);
		}
		
		JClassType enclose = toExplore.getEnclosingType();
		if (enclose != null) {
			exploreType(enclose);
		}
		
		exploreParameterizedType(toExplore);
		
		for (JField field : toExplore.getFields()) {
			exploreType(field.getType());
		}
	}
	
	private void exploreParameterizedType(JClassType toExplore) {
		JParameterizedType params = toExplore.isParameterized();
		if (params != null) {
			for (JClassType param : params.getTypeArgs()) {
				exploreType(param);
			}
		}
	}
	
	private void addExceptionClass(JClassType exceptionType) {
		if (exceptionType.getQualifiedSourceName().equals("java.lang.Exception"))
			return;
		if (exceptionType.getQualifiedSourceName().equals("java.lang.RuntimeException"))
			return;
		if (!exceptions.contains(exceptionType)) {
			exceptions.add(exceptionType);
			addExceptionClass(exceptionType.getSuperclass());
		}
	}
	
	private void exploreClassForException(JClassType toExplore) {
		for (JMethod method : toExplore.getMethods()) {
			for (JClassType throwClass : method.getThrows()) {
				addExceptionClass(throwClass);
			}
		}
	}
	
	private void explorePhpize(JClassType toPhpize) {
		Phpize phpize = toPhpize.getAnnotation(Phpize.class);
    	if (phpize != null) {
    		for (Class<?> clazz : phpize.value()) {
    			JClassType type = toPhpize.getOracle().findType(clazz.getCanonicalName());
    			if (type != null) {
    				exploreType(type);
    			}
    		}
    	}
	}
	
	@Override
	public RebindResult generateIncrementally(TreeLogger logger, 
		      GeneratorContextExt context, String typeName) 
		      throws UnableToCompleteException {
		
		JClassType remoteService = context.getTypeOracle().findType(typeName);
	    if (remoteService != null) {
	    	String rpcRelativePath = PhpRpcProxyCreator.getRemoteServiceRelativePath(remoteService);
	    	if (rpcRelativePath == null) {
		    	classes.clear();
		    	enums.clear();
		    	exceptions.clear();
		    	exploreClass(remoteService, true);
		    	exploreClassForException(remoteService);
		    	
		    	//explorePhpize(remoteService);
		    	
		    	removeTypeToBeNotConvertedToPhp(classes);
		    	removeTypeToBeNotConvertedToPhp(enums);
		    	
		    	// Create PhpServiceArtifact
		    	String serviceName = remoteService.getSimpleSourceName();
	    		String relativePath = PhpRpcProxyCreator.getPhpRemoteServiceRelativePath(remoteService);
		    	String serviceFilename = PhpRpcProxyCreator.getPhpRemoteServiceFilename(remoteService);
		    	if (serviceFilename == null)
		    		serviceFilename = serviceName + ".php";
		    	
		    	if (relativePath == null) {
		    		logger.log(TreeLogger.ERROR, "Annotation PhpRemoteServiceRelativePath is missing in service " + remoteService.getName());
		    		throw new UnableToCompleteException();
		    	}
		    	
	    		//System.out.println("   create " + relativePath);
	    		String generatedServiceFilename = PhpRpcProxyCreator.getServiceFilename(relativePath, remoteService);
	    		PhpServiceFileArtifact serviceArtifact = new PhpServiceFileArtifact(serviceName, generatedServiceFilename, serviceFilename);
	    		
	    		context.commitArtifact(logger, serviceArtifact);
	    		
	    		// IncludePaths
		    	try {
					ConfigurationProperty prop = context.getPropertyOracle().getConfigurationProperty("gwt.phprpc.includePaths");
					serviceArtifact.setIncludePaths(prop.getValues());
		    	} catch (BadPropertyValueException e1) {
					logger.log(TreeLogger.ERROR, e1.getMessage());
					throw new UnableToCompleteException();
				}
		    	
		    	// UniqueTypePhpFile
		    	boolean onePhpTypeFile = false;
		    	try {
		    		ConfigurationProperty prop = context.getPropertyOracle().getConfigurationProperty("phprpc.generateUniquePhpTypeFile");
		    		onePhpTypeFile = Boolean.valueOf(prop.getValues().get(0));
		    	} catch (BadPropertyValueException ee) {
		    		logger.log(TreeLogger.ERROR, ee.getMessage());
					throw new UnableToCompleteException();
		    	}
	    		
		    	HashMap<JClassType, PhpType> types = new HashMap<JClassType, PhpType>();
	    		for (JEnumType type : enums) {
	    			types.put(type, new PhpEnum(type));
	    		}
	    		for (JClassType type : classes) {
	    			boolean serializable = isSerializable(logger, context.getTypeOracle(), type);
	    			types.put(type, new PhpClass(type, serializable));
	    		}
	    		for (JClassType type : exceptions) {
	    			types.put(type, new PhpException(type));
	    		}
	    		
	    		if (onePhpTypeFile) {
	    			String filename = serviceName + ".types.php";
	    			PhpTypeFileArtifact artifact = new PhpTypeFileArtifact(serviceName, relativePath + "/" + filename);
	    			artifact.addSource(types.values());
	    			context.commitArtifact(logger, artifact);
	    			serviceArtifact.add(filename);
	    		}
	    		else {
	    			for (Map.Entry<JClassType, PhpType> entry : types.entrySet()) {
	    				String filename = getClassFilename(entry.getKey());
	    				PhpTypeFileArtifact artifact = new PhpTypeFileArtifact(serviceName, relativePath + "/" + filename);
	    				artifact.addSource(entry.getValue());
	    				context.commitArtifact(logger, artifact);
	    				serviceArtifact.add(filename);
	    			}
	    		}
	    		
	    		boolean generateServiceModel = false;
	    		try {
					ConfigurationProperty prop = context.getPropertyOracle().getConfigurationProperty("gwt.phprpc.generateServiceModel");
					generateServiceModel = Boolean.valueOf(prop.getValues().get(0));
	    		} catch (BadPropertyValueException e) {
					logger.log(TreeLogger.ERROR, e.getMessage());
					throw new UnableToCompleteException();
				}
	    		
	    		
	    		if (generateServiceModel) {
	    			context.commitArtifact(logger, new PhpServiceModelFileArtifact(serviceName, 
	    					remoteService, relativePath + "/" + serviceName + ".model.php"));
	    		}
	    	}
	    }
		
		return super.generateIncrementally(logger, context, typeName);
	}
	
	/*private String getEnclosingClassName(JClassType type) {
		if (type.getEnclosingType() == null)
			return type.getSimpleSourceName();
		else
			return getEnclosingClassName(type.getEnclosingType()) + "_" + type.getSimpleSourceName();
	}*/
	
	private String getClassFilename(JClassType type) {
		return PhpTools.typeToString(type, true) + ".php";
		//return getEnclosingClassName(type) + ".php";
	}
	
	private boolean isSerializable(TreeLogger logger, TypeOracle oracle, JClassType type) 
			throws UnableToCompleteException {
		if (serializableClass == null) {
			try {
				serializableClass = oracle.getType(Serializable.class.getName());
				isSerializableClass = oracle.getType(IsSerializable.class.getName());
			} catch (NotFoundException e) {
				logger.log(TreeLogger.ERROR, "Serializable or IsSerializable not found");
				throw new UnableToCompleteException();
			}
		}
		
		return serializableClass.isAssignableFrom(type) || isSerializableClass.isAssignableFrom(type);
	}
	
	private void removeTypeToBeNotConvertedToPhp(Set<? extends JClassType> types) {
		/*Iterator<? extends HasAnnotations> it = types.iterator();
		while (it.hasNext()) {
			HasAnnotations type = it.next();
			if (!type.isAnnotationPresent(ConvertToPhp.class))
				it.remove();
		}*/
	}

}
