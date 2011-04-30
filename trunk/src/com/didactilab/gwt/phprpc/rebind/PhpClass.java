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
import com.google.gwt.core.ext.typeinfo.JField;

public class PhpClass extends PhpType {

	private JClassType type;
	private boolean serializable;
	
	public PhpClass(JClassType type, boolean serializable) {
		super(type.getQualifiedSourceName());
		this.type = type;
		this.serializable = serializable;
	}
	
	@Override
	protected void getContents(TreeLogger logger, StringBuffer buffer)
			throws UnableToCompleteException {
		buffer.append("/**\n");
		buffer.append(" * @gwtname ").append(type.getQualifiedBinaryName()).append("\n");
		if (type.getEnclosingType() != null) {
			buffer.append(" * @enclosing ").append(type.getEnclosingType().getQualifiedBinaryName()).append("\n");
		}
		buffer.append(" */\n");
		buffer.append("class ").append(PhpTools.typeToString(type, true));
		JClassType superClass = type.getSuperclass();
		if ((superClass != null) && (!superClass.getQualifiedBinaryName().equals("java.lang.Object"))) {
			buffer.append(" extends ").append(PhpTools.typeToString(superClass, true));
		}
		buffer.append(" implements IsSerializable {\n");
		if (serializable) {
			for (JField field : type.getFields()) {
				if (field.isStatic()) 
					continue;
				if (field.isTransient())
					continue;
				buffer.append("\t/** @var ").append(PhpTools.typeToString(field.getType(), false)).append(" */\n");
				buffer.append("\tpublic $").append(field.getName()).append(";\n\n");
			}
		}
		buffer.append("}\n");
	}

}
