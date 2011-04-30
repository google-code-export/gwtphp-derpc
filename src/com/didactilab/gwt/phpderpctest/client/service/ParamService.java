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

import com.didactilab.gwt.phprpc.client.PhpRemoteServiceRelativePath;
import com.didactilab.gwt.phprpc.client.PhpService;

@PhpRemoteServiceRelativePath("pservice")
public interface ParamService extends PhpService {

	boolean boolTrueCall(boolean param);
	boolean boolFalseCall(boolean param);
	boolean byteCall(byte param);
	boolean charCall(char param);
	boolean charUTF8Call(char param);
	boolean doubleCall(double param);
	boolean floatCall(float param);
	boolean intCall(int param);
	boolean longCall(long param);
	boolean shortCall(short param);
	boolean stringCall(String param);
	boolean stringUTF8Call(String param);
	boolean objectCall(CustomObject param);
	boolean enumCall(CustomEnum param);
	boolean intArrayCall(int[][] param);
	boolean stringArrayCall(String[] param);
	
	boolean boolTrueObjectCall(Boolean param);
	boolean boolFalseObjectCall(Boolean param);
	boolean byteObjectCall(Byte param);
	boolean charObjectCall(Character param);
	boolean doubleObjectCall(Double param);
	boolean floatObjectCall(Float param);
	boolean intObjectCall(Integer param);
	boolean longObjectCall(Long param);
	boolean shortObjectCall(Short param);
	boolean intObjectArrayCall(Integer[][] param);
	
}
