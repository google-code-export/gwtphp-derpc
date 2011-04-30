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

import com.google.gwt.core.ext.TreeLogger;
import com.google.gwt.core.ext.UnableToCompleteException;
import com.google.gwt.core.ext.typeinfo.JClassType;
import com.google.gwt.core.ext.typeinfo.JMethod;
import com.google.gwt.core.ext.typeinfo.JParameter;
import com.google.gwt.core.ext.typeinfo.JType;

@SuppressWarnings("serial")
public class PhpServiceModelFileArtifact extends PhpFileArtifact {
	
	private JClassType type;

	public PhpServiceModelFileArtifact(String serviceName, JClassType serviceClass, String partialPath) {
		super(serviceName, partialPath);
		this.type = serviceClass;
	}

	@Override
	protected void getContents(TreeLogger logger, StringBuffer buffer)
			throws UnableToCompleteException {
		buffer.append("<?php\n\n");
		buffer.append("/** @gwtname ").append(type.getQualifiedSourceName()).append(" */\n");
		if (type.getEnclosingType() != null) {
			buffer.append("/** @enclosing ").append(type.getEnclosingType().getQualifiedSourceName()).append(" */\n");
		}
		buffer.append("class ").append(type.getName()).append(" implements RemoteService {\n\n");
		
		for (JMethod method : type.getMethods()) {
			JType returnType = method.getReturnType();
			String returnTypeString = PhpTools.typeToString(returnType, false);
			if (method.getParameters().length != 0) {
				buffer.append("\t/**\n");
				for (JParameter param : method.getParameters()) {
					buffer.append("\t * @param ").append(PhpTools.typeToString(param.getType(), false));
					buffer.append(" $").append(param.getName()).append("\n");
				}
				buffer.append("\t * @return ").append(returnTypeString).append("\n");
				buffer.append("\t */\n");
			}
			else {
				buffer.append("\t/** @return ").append(returnTypeString).append(" */\n");
			}
			buffer.append("\t").append(PhpTools.getPhpVisibility(method)).append(" function ").append(method.getName()).append("(");
			JParameter[] params = method.getParameters();
			for (int i=0, c=params.length; i<c; i++) {
				buffer.append("$").append(params[i].getName());
				if (i < c - 1)
					buffer.append(", ");
			}
			buffer.append(") {\n\n");
			buffer.append("\t}\n\n");
		}
		buffer.append("}\n");
	}

}
