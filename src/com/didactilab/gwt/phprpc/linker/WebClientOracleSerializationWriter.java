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
package com.didactilab.gwt.phprpc.linker;

import java.io.OutputStream;
import java.lang.reflect.Field;
import java.lang.reflect.Modifier;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;
import java.util.Map;

import com.google.gwt.user.client.rpc.SerializationException;

public class WebClientOracleSerializationWriter extends WebClientOracleWriter {
	
	public WebClientOracleSerializationWriter(OutputStream out) {
		super(out);
	}

	public void writeNull() throws SerializationException {
		write("N;");
	}
	
	public void writeInteger(int value) throws SerializationException {
		write("i:" + value + ";");
	}
	
	public void writeString(String value) throws SerializationException {
		write("s:" + value.length() + ":\"" + value + "\";");
	}
	
	public void writeCollection(Collection<?> collection) throws SerializationException {
		write("a:" + collection.size() + ":{");
		int index = 0;
		for (Object obj : collection) {
			writeInteger(index++);
			writeObject(obj);
		}
		write("}");
	}
	
	public void writeMap(Map<?, ?> map) throws SerializationException {
		write("a:" + map.size() + ":{");
		for (Map.Entry<?, ?> entry : map.entrySet()) {
			writeObject(entry.getKey());
			writeObject(entry.getValue());
		}
		write("}");
	}
	
	public void writeField(String className, Field field, Object instance) throws SerializationException {
		try {
			String name = field.getName();
			if (Modifier.isPrivate(field.getModifiers()))
				name = "\0" + className + "\0" + name;
			else if (Modifier.isProtected(field.getModifiers()))
				name = "\0*\0" + name;
			writeString(name);
			boolean accessible = field.isAccessible();
			field.setAccessible(true);
			Class<?> type = field.getType();
			if (type.equals(int.class)) {
				int value = field.getInt(instance);
				writeInteger(value);
			}
			else {
				writeObject(field.get(instance));
			}
			field.setAccessible(accessible);
		}
		catch (IllegalAccessException e) {
			throw new SerializationException(e.getMessage());
		}
	}
	
	private List<Field> getFields(Class<?> clazz) {
		Field[] fields = clazz.getDeclaredFields();
		ArrayList<Field> newFields = new ArrayList<Field>();
		for (Field field : fields) {
			if (Modifier.isTransient(field.getModifiers()))
				continue;
			if (field.getName().startsWith("$"))
				continue;
			newFields.add(field);
		}
		return newFields;
	}
	
	public void writeObject(Object instance, String renameClass) throws SerializationException {
		if (instance == null) {
			writeNull();
			return;
		}
		Class<?> clazz = instance.getClass();
		if (clazz.equals(String.class)) {
			writeString((String) instance);
		}
		else if (Collection.class.isAssignableFrom(clazz)) {
			Collection<?> coll = (Collection<?>) instance;
			writeCollection(coll);
		}
		else if (Map.class.isAssignableFrom(clazz)) {
			Map<?, ?> map = (Map<?, ?>) instance;
			writeMap(map);
		}
		else {
			String name;
			if (renameClass != null)
				name = renameClass;
			else
				name = clazz.getSimpleName();
			write("O:" + name.length() + ":\"" + name + "\":");
			
			List<Field> fields = getFields(clazz);
			write(String.valueOf(fields.size()) + ":{");
			for (Field field : fields) {
				writeField(name, field, instance);
			}
			write("}");
		}
	}
	
}
