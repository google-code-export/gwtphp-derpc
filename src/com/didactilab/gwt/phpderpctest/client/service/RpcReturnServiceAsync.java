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

import com.google.gwt.user.client.rpc.AsyncCallback;

public interface RpcReturnServiceAsync {

	void boolTrueReturn(AsyncCallback<Boolean> callback);
	
	void boolFalseReturn(AsyncCallback<Boolean> callback);

	void byteReturn(AsyncCallback<Byte> callback);

	void charReturn(AsyncCallback<Character> callback);
	
	void charUTF8Return(AsyncCallback<Character> callback);

	void doubleReturn(AsyncCallback<Double> callback);

	void enumReturn(AsyncCallback<CustomEnum> callback);

	void floatReturn(AsyncCallback<Float> callback);

	void intReturn(AsyncCallback<Integer> callback);

	void longReturn(AsyncCallback<Long> callback);

	void noReturn(AsyncCallback<Void> callback);

	void objectReturn(AsyncCallback<CustomObject> callback);

	void shortReturn(AsyncCallback<Short> callback);

	void stringReturn(AsyncCallback<String> callback);
	
	void stringEscapeReturn(AsyncCallback<String> callback);
	
	void stringUTF8Return(AsyncCallback<String> callback);

	void intArrayReturn(AsyncCallback<int[][]> callback);

	void stringArrayReturn(AsyncCallback<String[]> callback);
	
	void throwReturn(AsyncCallback<Void> callback);
}
