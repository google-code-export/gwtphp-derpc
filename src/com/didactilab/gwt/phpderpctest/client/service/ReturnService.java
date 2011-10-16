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
public interface ReturnService extends PhpService {

	void noReturn();
	boolean boolTrueReturn();
	boolean boolFalseReturn();
	byte byteReturn();
	char charReturn();
	char charUTF8Return();
	double doubleReturn();
	float floatReturn();
	int intReturn() throws CustomException;
	long longReturn();
	short shortReturn();
	String stringReturn();
	String stringEscapeReturn();
	String stringUTF8Return();
	CustomObject objectReturn();
	CustomEnum enumReturn();
	int[][] intArrayReturn();
	String[] stringArrayReturn();
	
	void throwReturn() throws CustomException;
	
}
