package com.didactilab.gwt.phprpc.rebind;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashSet;
import java.util.Iterator;
import java.util.LinkedList;
import java.util.List;
import java.util.ListIterator;
import java.util.Set;

import com.didactilab.gwt.phprpc.client.DoNotPhpize;
import com.didactilab.gwt.phprpc.client.Phpize;
import com.didactilab.gwt.phprpc.rebind.phpgen.PhpClass;
import com.didactilab.gwt.phprpc.rebind.phpgen.PhpEnum;
import com.didactilab.gwt.phprpc.rebind.phpgen.PhpException;
import com.didactilab.gwt.phprpc.rebind.phpgen.PhpType;
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
import com.google.gwt.core.ext.typeinfo.JTypeParameter;
import com.google.gwt.core.ext.typeinfo.JWildcardType;
import com.google.gwt.core.ext.typeinfo.NotFoundException;
import com.google.gwt.core.ext.typeinfo.TypeOracle;
import com.google.gwt.user.client.rpc.IsSerializable;

public abstract class PhpFilesGenerator {

	public static class DeRpc extends PhpFilesGenerator {

		public DeRpc(TreeLogger logger, GeneratorContextExt context, String typeName) throws UnableToCompleteException {
			super(logger, context, typeName);
		}

		@Override
		protected PhpServiceFileArtifact createServiceFileArtifact(String serviceName, 
				String partialPath, String serviceFilename) {
			return new PhpServiceFileArtifact.DeRpc(serviceName, partialPath, serviceFilename);
		}

	}
	
	public static class Rpc extends PhpFilesGenerator {

		public Rpc(TreeLogger logger, GeneratorContextExt context, String typeName) throws UnableToCompleteException {
			super(logger, context, typeName);
		}

		@Override
		protected PhpServiceFileArtifact createServiceFileArtifact(String serviceName, 
				String partialPath, String serviceFilename) {
			return new PhpServiceFileArtifact.Rpc(serviceName, partialPath, serviceFilename);
		}

	}
	
	private static final String PROP_SERVICE_MODEL = "phprpc.generator.servicemodel";
	private static final String PROP_PHPFILETYPE = "phprpc.generator.phpfiletype";
	private static final String PROP_INCLUDE_PATHS = "phprpc.includepaths";
	private static final String PROP_EXCLUDE_EXCEPTIONS = "phprpc.generator.exclude.exceptions";
	private static final String PROP_EXCLUDE_CLASSES = "phprpc.generator.exclude.classes";

	private TreeLogger logger;
	private GeneratorContextExt context;
	private String typeName;
	
	private ArrayList<JClassType> phpizableClasses = new ArrayList<JClassType>();
	private LinkedList<JType> exploringTypes= new LinkedList<JType>();

	private HashSet<String> excludedExceptions = new HashSet<String>();
	private HashSet<String> excludedClasses = new HashSet<String>();
	
	private HashSet<String> exclusionMatchers = new HashSet<String>();
	private HashSet<String> exclusions = new HashSet<String>();
	
	private ArrayList<String> includePaths = new ArrayList<String>();
	private PhpFileType phpFileType;
	private boolean generateServiceModel;
	
	private JClassType isSerializableClass;
	private JClassType serializableClass;
	private JClassType exceptionClass;

	public PhpFilesGenerator(TreeLogger logger, GeneratorContextExt context, String typeName) throws UnableToCompleteException {
		this.logger = logger;
		this.context = context;
		this.typeName = typeName;
		
		try {
			TypeOracle oracle = context.getTypeOracle();
			serializableClass = oracle.getType(Serializable.class.getName());
			isSerializableClass = oracle.getType(IsSerializable.class.getName());
			exceptionClass = context.getTypeOracle().findType(Exception.class.getName());
		} catch (NotFoundException e) {
			logger.log(TreeLogger.ERROR, "Serializable or IsSerializable or Exception class not found");
			throw new UnableToCompleteException();
		}
	}

	public void generate() throws UnableToCompleteException {
		JClassType remoteService = context.getTypeOracle().findType(typeName);
		if (remoteService == null) {
			return;
		}
		
		String rpcRelativePath = PhpDerpcProxyCreator.getRemoteServiceRelativePath(remoteService);
		if (rpcRelativePath != null) {
			return;
		}
		
		/*System.out.println("+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");
		System.out.println("+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");
		System.out.println("+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");*/
		
		readProperties();
		prepareExclusions();
		
		readDoNotPhpize(remoteService);
		
		exploringTypes.clear();
		phpizableClasses.clear();
		
		exploreService(remoteService);
		
		/*System.out.println("~~~~~~~~BEFORE STEP 1~~~~~~~~~~~~~");
		debugPrint(exploringTypes);
		System.out.println("~~~~~~~~~~~~~~~~~~~~~");*/
		
		simplify();
		
		/*System.out.println("~~~~~~~~~AFTER STEP 1~~~~~~~~~~~~");
		debugPrint(exploringTypes);
		System.out.println("~~~~~~~~~~~~~~~~~~~~~");*/
		
		boolean hasAdded = true;
		int count = 0;
		while (hasAdded && count < 30) {
			hasAdded = findFieldsAndAnnotations();
			count++;
		}
		if (count == 30) {
			throw new UnableToCompleteException();
		}
		
		/*System.out.println("~~~~~~~~~~~AFTER STEP 2~~~~~~~~~~");
		debugPrint(exploringTypes);
		System.out.println("~~~~~~~~~~~~~~~~~~~~~");*/
		
		simplify();
		removeDuplicates();
		
		/*System.out.println("~~~~~~~~~~~AFTER STEP 3~~~~~~~~~~");
		debugPrint(phpizableClasses);
		System.out.println("~~~~~~~~~~~~~~~~~~~~~");*/

		// Create PhpServiceArtifact
		String serviceName = remoteService.getSimpleSourceName();
		String relativePath = PhpDerpcProxyCreator.getPhpRemoteServiceRelativePath(remoteService);
		String serviceFilename = PhpDerpcProxyCreator.getPhpRemoteServiceFilename(remoteService);
		if (serviceFilename == null)
			serviceFilename = serviceName + ".php";

		if (relativePath == null) {
			logger.log(TreeLogger.ERROR, "Annotation PhpRemoteServiceRelativePath is missing in service " + remoteService.getName());
			throw new UnableToCompleteException();
		}

		String generatedServiceFilename = PhpDerpcProxyCreator.getServiceFilename(relativePath, remoteService);
		PhpServiceFileArtifact serviceArtifact = createServiceFileArtifact(serviceName, generatedServiceFilename, serviceFilename);

		context.commitArtifact(logger, serviceArtifact);

		serviceArtifact.setIncludePaths(includePaths);
		
		ArrayList<PhpType> phpTypes = new ArrayList<PhpType>();
		for (JClassType type : phpizableClasses) {
			PhpType phpType;
			if (type instanceof JEnumType) {
				phpType = new PhpEnum(type.isEnum());
			} else if (isExceptionClass(type)) {
				phpType = new PhpException(type);
			} else {
				boolean serializable = isSerializable(type);
				phpType = new PhpClass(type, serializable);
			}
			phpTypes.add(phpType);
		}
		
		/*System.out.println("=============");
		for (PhpType phpType : phpTypes) {
			System.out.println(phpType.getJavaType().getSimpleSourceName());
		}*/

		if (phpFileType == PhpFileType.ONE_FILE) {
			String filename = serviceName + ".types.php";
			PhpTypeFileArtifact artifact = new PhpTypeFileArtifact(serviceName, relativePath + "/" + filename);
			artifact.addSource(phpTypes);
			context.commitArtifact(logger, artifact);
			serviceArtifact.add(filename);
		} else {
			for (PhpType phpType : phpTypes) {
				String filename = phpType.getDefaultFilename() + ".php";
				PhpTypeFileArtifact artifact = new PhpTypeFileArtifact(serviceName, relativePath + "/" + filename);
				artifact.addSource(phpType);
				context.commitArtifact(logger, artifact);
				serviceArtifact.add(filename);
			}
		}

		if (generateServiceModel) {
			context.commitArtifact(logger, new PhpServiceModelFileArtifact(serviceName, remoteService, relativePath + "/" + serviceName + ".model.php"));
		}
	}

	protected abstract PhpServiceFileArtifact createServiceFileArtifact(String serviceName, String partialPath, String serviceFilename);

	private void addPhpizableClass(JClassType classType) {
		if (!phpizableClasses.contains(classType)) {
			phpizableClasses.add(classType);
		}
	}
	
	private void exploreService(JClassType classType) {
		for (JMethod method : classType.getMethods()) {
			JType returnType = method.getReturnType();
			
			addExploringType(returnType);
			for (JType type : method.getParameterTypes()) {
				addExploringType(type);
			}
			for (JClassType throwClass : method.getThrows()) {
				addExploringType(throwClass);
			}
		}
	}
	
	private boolean addExploringType(JType type) {
		if (type.isArray() != null) {
			type = type.getLeafType();
		}
		
		if (type.isPrimitive() != null) {
			return false;
		}
		
		if (exploringTypes.contains(type)) {
			return false;
		}
		
		if (type.isInterface() != null) {
			return false;
		}
		
		if (isJavaLangType(type)) {
			return false;
		}
		
		boolean adding = false;
		
		if (type.isClass() != null) {
			Set<? extends JClassType> parentSet = ((JClassType) type).getFlattenedSupertypeHierarchy();
			ArrayList<? extends JClassType> parentList = new ArrayList<JClassType>(parentSet);
			for (ListIterator<? extends JClassType> iterator = parentList.listIterator(parentList.size()); iterator.hasPrevious();) {
				JClassType classType = (JClassType) iterator.previous();
				if (classType.isInterface() != null) {
					continue;
				}
				if (isJavaLangType(classType)) {
					continue;
				}
				exploringTypes.add(classType);
				adding = true;
			}
		} else {
			exploringTypes.add(type);
			adding = true;
		}
		
		return adding;
	}
	
	private void simplify() {
		boolean newType = true;
		ArrayList<JType> exploring = new ArrayList<JType>();
		while (newType) {
			newType = false;
			exploring.clear();
			exploring.addAll(exploringTypes);
			exploringTypes.clear();
			for (JType type : exploring) {
				if (type.isArray() != null) {
					// is array
					addExploringType(type.getLeafType());
					newType = true;
					//System.out.println("<<<<<< array");
				} else if (type.isTypeParameter() != null) {
					JTypeParameter paramType = type.isTypeParameter();
					for (JClassType classType : paramType.getBounds()) {
						addExploringType(classType);
						//System.out.println("<<<<<< type parameter");
						newType = true;
					}
				} else if (type.isWildcard() != null) {
					JWildcardType wtype = type.isWildcard();
					addExploringType(wtype.getBaseType());
					newType = true;
					//System.out.println("<<<<<< wildcard");
					for (JClassType classType : wtype.getLowerBounds()) {
						addExploringType(classType);
					}
					for (JClassType classType : wtype.getUpperBounds()) {
						addExploringType(classType);
					}
				} else if (type.isParameterized() != null) {
					JParameterizedType params = type.isParameterized();
					
					for (JClassType param : params.getTypeArgs()) {
						addExploringType(param);
					}
					
					exploringTypes.add(params.getRawType());
					newType = true;
					//System.out.println("<<<<<< parametized");
				} else {
					exploringTypes.add(type);
				}
			}
			
			// Remove all excluded classes
			for (Iterator<JType> iterator = exploringTypes.iterator(); iterator.hasNext();) {
				JType type = (JType) iterator.next();
				if (isExcluded(type)) {
					iterator.remove();
				}
			}
		} 
	}
	
	private boolean findFieldsAndAnnotations() {
		ArrayList<JType> exploring = new ArrayList<JType>(exploringTypes);
		//exploringTypes.clear();
		
		boolean adding = false;
		for (JType type : exploring) {
			JClassType classType = type.isClass();
			if (classType != null && classType.isEnum() == null) {
				if (readPhpize(classType)) {
					adding = true;
				}
				//System.out.println("#### Read class " + classType.getParameterizedQualifiedSourceName());
				for (JField field : classType.getFields()) {
					//System.out.println("#### add field " + field.getName() + " >> " + field.getType().getParameterizedQualifiedSourceName());
					if (addExploringType(field.getType())) {
						adding = true;
					}
				}
			}
			//exploringTypes.add(type);
		}
		return adding;
	}
	
	private void removeDuplicates() {
		HashSet<JClassType> types = new HashSet<JClassType>();
		for (JType type : exploringTypes) {
			JClassType classType = type.isClass();
			if (classType == null) {
				System.out.println("ERROR: " + type.getQualifiedSourceName() + " is not a class");
			} else {
				if (types.add(classType)) {
					addPhpizableClass(classType);
				}
			}
		}
		
		exploringTypes.clear();
	}
	
	private boolean isJavaLangType(JType type) {
		return type.getQualifiedSourceName().startsWith("java.lang.");
	}
	
	private boolean readPhpize(JClassType classType) {
		boolean adding = false;
		Phpize phpize = classType.getAnnotation(Phpize.class);
		if (phpize != null) {
			for (Class<?> clazz : phpize.value()) {
				JClassType type = classType.getOracle().findType(clazz.getCanonicalName());
				if (type != null) {
					if (addExploringType(type)) {
						adding = true;
					}
				}
			}
		}
		return adding;
	}
	
	private void readDoNotPhpize(JClassType classType) {
		DoNotPhpize phpize = classType.getAnnotation(DoNotPhpize.class);
		if (phpize != null) {
			for (Class<?> clazz : phpize.value()) {
				if (clazz != null) {
					exclusions.add(clazz.getName());
				}
			}
		}
	}
	
	private boolean isExceptionClass(JClassType classType) {
		return exceptionClass.isAssignableFrom(classType);
	}

	private boolean isSerializable(JClassType classType) {
		return serializableClass.isAssignableFrom(classType) || isSerializableClass.isAssignableFrom(classType);
	}
	
	private void readProperties() throws UnableToCompleteException {
		includePaths.clear();
		excludedClasses.clear();
		excludedExceptions.clear();
		try {
			excludedClasses.addAll(readProperty(PROP_EXCLUDE_CLASSES));
			excludedExceptions.addAll(readProperty(PROP_EXCLUDE_EXCEPTIONS));
			includePaths.addAll(readProperty(PROP_INCLUDE_PATHS));
			phpFileType = PhpFileType.valueOfName(readProperty(PROP_PHPFILETYPE).get(0));
			generateServiceModel = Boolean.valueOf(readProperty(PROP_SERVICE_MODEL).get(0));
		} catch (BadPropertyValueException e) {
			logger.log(TreeLogger.ERROR, e.getMessage());
			throw new UnableToCompleteException();
		} catch (IllegalArgumentException e) {
			logger.log(TreeLogger.ERROR, e.getMessage());
			throw new UnableToCompleteException();
		}
	}
	
	private void prepareExclusions() {
		for (String excludedClass : excludedClasses) {
			if (excludedClass.startsWith("~")) {
				String pattern = excludedClass.substring(1);
				exclusionMatchers.add(pattern);
			} else {
				exclusions.add(excludedClass);
			}
		}
		for (String excludedClass : excludedExceptions) {
			if (excludedClass.startsWith("~")) {
				String pattern = excludedClass.substring(1);
				exclusionMatchers.add(pattern);
			} else {
				exclusions.add(excludedClass);
			}
		}
		
		/*System.out.println("Preparation of exclusions :");
		for (String ex : exclusions) {
			System.out.println(" - " + ex);
		}
		for (String ex : exclusionMatchers) {
			System.out.println(" ~ " + ex);
		}*/
		
	}
	
	private boolean isExcluded(JType type) {
		String typeName = type.getQualifiedSourceName();
		if (exclusions.contains(typeName)) {
			return true;
		}
		
		for (String matcher : exclusionMatchers) {
			if (typeName.matches(matcher)) {
				return true;
			}
		}
		
		return false;
	}

	private List<String> readProperty(String name) throws BadPropertyValueException {
		ConfigurationProperty prop = context.getPropertyOracle().getConfigurationProperty(name);
		return prop.getValues();
	}
	
	protected void debugPrint(Collection<? extends JType> types) {
		for (JType type : types) {
			System.out.println(":: " + type.getParameterizedQualifiedSourceName());
		}
	}

}
