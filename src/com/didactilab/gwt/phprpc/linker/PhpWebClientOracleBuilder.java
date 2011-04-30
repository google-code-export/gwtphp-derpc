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

import java.io.IOException;
import java.io.OutputStream;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;

import com.google.gwt.rpc.server.CastableTypeData;
import com.google.gwt.user.client.rpc.SerializationException;

@SuppressWarnings("serial")
public class PhpWebClientOracleBuilder implements Serializable {

	@SuppressWarnings("unused")
	private static class ClassData implements Serializable {
		public CastableTypeData castableTypeData;
		public final Map<String, String> fieldIdentsToNames = new HashMap<String, String>();
		public final Map<String, String> fieldNamesToIdents = new HashMap<String, String>();
		public final Map<String, String> methodJsniNamesToIdents = new HashMap<String, String>();
		public int queryId;
		public String seedName;
		public List<String> serializableFields = Collections.emptyList();
		public String typeName;
	}

	protected final Map<String, ClassData> classData = new HashMap<String, ClassData>();

	protected final Set<String> idents = new HashSet<String>();

	protected final Map<String, ClassData> seedNamesToClassData = new HashMap<String, ClassData>();

	public void add(String jsIdent, String jsniIdent, String className,
			String memberName, int queryId, CastableTypeData castableTypeData) {

		idents.add(jsIdent);
		ClassData data = getClassData(className);

		/*
		 * Don't overwrite castableTypeData and queryId if already set. There
		 * are many versions of symbols for a given className, corresponding to
		 * the type of member fields, etc., which don't have the queryId or
		 * castableTypeData initialized. Only the symbol data for the class
		 * itself has this info.
		 */
		if (data.castableTypeData == null) {
			data.queryId = queryId;
			data.castableTypeData = castableTypeData;
		}

		if (jsniIdent == null || jsniIdent.length() == 0) {
			data.typeName = className;
			data.seedName = jsIdent;
			seedNamesToClassData.put(jsIdent, data);
		} else {
			if (jsniIdent.contains("(")) {
				jsniIdent = jsniIdent.substring(jsniIdent.indexOf("::") + 2,
						jsniIdent.indexOf(')') + 1);
				data.methodJsniNamesToIdents.put(jsniIdent, jsIdent);
			} else {
				data.fieldIdentsToNames.put(jsIdent, memberName);
				data.fieldNamesToIdents.put(memberName, jsIdent);
			}
		}
	}

	public void setSerializableFields(String className, List<String> fieldNames) {
		ClassData data = getClassData(className);
		assert data.serializableFields == null
				|| fieldNames.containsAll(data.serializableFields);
		if (fieldNames.size() == 1) {
			data.serializableFields = Collections.singletonList(fieldNames
					.get(0));
		} else {
			data.serializableFields = new ArrayList<String>(fieldNames);
			Collections.sort(data.serializableFields);
		}
	}

	private ClassData getClassData(String className) {
		ClassData toReturn = classData.get(className);
		if (toReturn == null) {
			toReturn = new ClassData();
			classData.put(className, toReturn);
		}
		return toReturn;
	}
	
	public void store(OutputStream out) throws IOException {
		WebClientOracleSerializationWriter writer = new WebClientOracleSerializationWriter(out);
		try {
			writer.begin();
			writer.writeObject(this, "WebModeClientOracle");
			writer.finish();
		} catch (SerializationException e) {
			throw new IOException(e.getMessage());
		}
	}

}
