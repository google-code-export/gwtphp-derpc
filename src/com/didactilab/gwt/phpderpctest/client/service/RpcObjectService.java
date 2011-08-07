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

import com.didactilab.gwt.phpderpctest.client.sample.SampleException;
import com.didactilab.gwt.phpderpctest.client.service.CustomObject.CustomSubObject;
import com.didactilab.gwt.phprpc.client.PhpRemoteService;
import com.didactilab.gwt.phprpc.client.PhpRemoteServiceRelativePath;
import com.didactilab.gwt.phprpc.client.Phpize;

@PhpRemoteServiceRelativePath("pservice")
@Phpize(SampleException.class)
public interface RpcObjectService extends PhpRemoteService {

	boolean paramCall(ComplexCustomObject param);
	ComplexCustomObject returnTest();
	
	boolean arrayListCall(ArrayList<Boolean> list);
	ArrayList<CustomObject> arrayListReturn();
	
	boolean hashMapCall(HashMap<String, String> map);
	HashMap<Integer, String> hashMapReturn();
	
	boolean hashSetCall(HashSet<Integer> set);
	HashSet<String> hashSetReturn();
	
	boolean dateCall(Date date);
	Date dateReturn();
	
	boolean customSubObjectCall(CustomSubObject obj);
	CustomSubObject customSubObjectReturn();
	
}
