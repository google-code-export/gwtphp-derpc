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

import com.google.gwt.core.ext.typeinfo.JArrayType;
import com.google.gwt.core.ext.typeinfo.JClassType;
import com.google.gwt.core.ext.typeinfo.JMethod;
import com.google.gwt.core.ext.typeinfo.JParameterizedType;
import com.google.gwt.core.ext.typeinfo.JType;

public class PhpTools {
	
	private static final String PRIVATE_VISIBILITY = "private";
	private static final String PROTECTED_VISIBILITY = "protected";
	private static final String PUBLIC_VISIBILITY = "public";

	private static String filterType(String type) {
		if ("String".equals(type))
			return "string";
		else if ("boolean".equals(type)) 
			return "bool";
		else
			return type;
	}
	
	public static String typeToString(JType type, boolean phpCompatible) {
		String name = "";
		JArrayType arrayType = type.isArray();
		if (arrayType != null) {
			if (phpCompatible)
				name = "array";
			else
				name = typeToString(arrayType.getComponentType(), phpCompatible) + "[]";
		}
		else {
			JClassType classType = type.isClassOrInterface();
			if ((classType != null) && (classType.getEnclosingType() != null))
				name = typeToString(classType.getEnclosingType(), phpCompatible) + "_";
			name += filterType(type.getSimpleSourceName());
			if (!phpCompatible) {
				JParameterizedType params = type.isParameterized();
				if (params != null) {
					JClassType[] args = params.getTypeArgs();
					name += "<";
					for (int i=0, c=args.length; i<c; i++) {
						name += typeToString(args[i], phpCompatible);
						if (i < c - 1)
							name += ", ";
					}
					name += ">";
				}
			}
		}
		return name;
	}
	
	public static String getPhpVisibility(JMethod method) {
		if (method.isPrivate())
			return PRIVATE_VISIBILITY;
		else if (method.isProtected())
			return PROTECTED_VISIBILITY;
		else 
			return PUBLIC_VISIBILITY;
	}
	
}
