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

public interface ParamServiceAsync {

	void boolFalseCall(boolean param, AsyncCallback<Boolean> callback);

	void boolTrueCall(boolean param, AsyncCallback<Boolean> callback);

	void byteCall(byte param, AsyncCallback<Boolean> callback);

	void charCall(char param, AsyncCallback<Boolean> callback);

	void charUTF8Call(char param, AsyncCallback<Boolean> callback);

	void doubleCall(double param, AsyncCallback<Boolean> callback);

	void enumCall(CustomEnum param, AsyncCallback<Boolean> callback);

	void floatCall(float param, AsyncCallback<Boolean> callback);

	void intArrayCall(int[][] param, AsyncCallback<Boolean> callback);

	void intCall(int param, AsyncCallback<Boolean> callback);

	void longCall(long param, AsyncCallback<Boolean> callback);

	void objectCall(CustomObject param, AsyncCallback<Boolean> callback);

	void shortCall(short param, AsyncCallback<Boolean> callback);

	void stringArrayCall(String[] param, AsyncCallback<Boolean> callback);

	void stringCall(String param, AsyncCallback<Boolean> callback);

	void stringUTF8Call(String param, AsyncCallback<Boolean> callback);

	void intObjectCall(Integer param, AsyncCallback<Boolean> callback);

	void boolFalseObjectCall(Boolean param, AsyncCallback<Boolean> callback);

	void boolTrueObjectCall(Boolean param, AsyncCallback<Boolean> callback);

	void charObjectCall(Character param, AsyncCallback<Boolean> callback);

	void shortObjectCall(Short param, AsyncCallback<Boolean> callback);

	void longObjectCall(Long param, AsyncCallback<Boolean> callback);

	void floatObjectCall(Float param, AsyncCallback<Boolean> callback);

	void doubleObjectCall(Double param, AsyncCallback<Boolean> callback);

	void byteObjectCall(Byte param, AsyncCallback<Boolean> callback);
	
	void intObjectArrayCall(Integer[][] param, AsyncCallback<Boolean> callback);
	
}
