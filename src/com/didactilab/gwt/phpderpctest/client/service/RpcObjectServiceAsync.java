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
package com.didactilab.gwt.phpderpctest.client.service;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.HashSet;

import com.didactilab.gwt.phpderpctest.client.service.CustomObject.CustomSubObject;
import com.google.gwt.user.client.rpc.AsyncCallback;

public interface RpcObjectServiceAsync {

	void paramCall(ComplexCustomObject param, AsyncCallback<Boolean> callback);

	void returnTest(AsyncCallback<ComplexCustomObject> callback);

	void arrayListCall(ArrayList<Boolean> list, AsyncCallback<Boolean> callback);
	
	void arrayListReturn(AsyncCallback<ArrayList<CustomObject>> callback);

	void hashMapCall(HashMap<String, String> map, AsyncCallback<Boolean> callback);

	void hashMapReturn(AsyncCallback<HashMap<Integer, String>> callback);

	void hashSetReturn(AsyncCallback<HashSet<String>> callback);

	void hashSetCall(HashSet<Integer> set, AsyncCallback<Boolean> callback);

	void dateCall(Date date, AsyncCallback<Boolean> callback);
	
	void dateReturn(AsyncCallback<Date> callback);
	
	void customSubObjectCall(CustomSubObject obj, AsyncCallback<Boolean> callback);
	
	void customSubObjectReturn(AsyncCallback<CustomSubObject> callback);
	
	void arrayObjectEmptyReturn(AsyncCallback<CustomArrayObject> callback);
	
}
