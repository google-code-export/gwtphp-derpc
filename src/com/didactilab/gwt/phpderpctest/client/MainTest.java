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
package com.didactilab.gwt.phpderpctest.client;

import com.didactilab.gwt.phpderpctest.client.objecttest.ArrayListCallTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.*;
import com.didactilab.gwt.phpderpctest.client.paramtest.*;
import com.didactilab.gwt.phpderpctest.client.returntest.*;
import com.didactilab.gwt.phpderpctest.client.visual.TestBench;
import com.google.gwt.core.client.EntryPoint;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.TabPanel;

public class MainTest implements EntryPoint {
	
	private TabPanel panel = new TabPanel();
	private TestBench returnBench = new TestBench("Return test");
	private TestBench paramBench = new TestBench("Param test");
	private TestBench objectBench = new TestBench("Object test");
	
	@Override
	public void onModuleLoad() {
		RootPanel.get().add(panel);
		
		panel.add(returnBench, "Return test");
		panel.add(paramBench, "Param test");
		panel.add(objectBench, "Object test");
		
		panel.selectTab(0);
		
		initReturnBench();
		initParamBench();
		initObjectBench();
	}
	
	private void initReturnBench() {
		returnBench.add(new NoReturnTest());
		returnBench.add(new BoolFalseReturnTest());
		returnBench.add(new BoolTrueReturnTest());
		returnBench.add(new ByteReturnTest());
		returnBench.add(new CharReturnTest());
		returnBench.add(new CharUTF8ReturnTest());
		returnBench.add(new DoubleReturnTest());
		returnBench.add(new FloatReturnTest());
		returnBench.add(new IntReturnTest());
		returnBench.add(new LongReturnTest());
		returnBench.add(new ShortReturnTest());
		returnBench.add(new StringReturnTest());
		returnBench.add(new StringUTF8ReturnTest());
		returnBench.add(new ObjectReturnTest());
		returnBench.add(new EnumReturnTest());
		returnBench.add(new IntArrayReturnTest());
		returnBench.add(new StringArrayReturnTest());
	}
	
	private void initParamBench() {
		paramBench.add(new BoolFalseCallTest());
		paramBench.add(new BoolTrueCallTest());
		paramBench.add(new ByteCallTest());
		paramBench.add(new CharCallTest());
		paramBench.add(new CharUTF8CallTest());
		paramBench.add(new DoubleCallTest());
		paramBench.add(new EnumCallTest());
		paramBench.add(new FloatCallTest());
		paramBench.add(new IntArrayCallTest());
		paramBench.add(new IntCallTest());
		paramBench.add(new LongCallTest());
		paramBench.add(new ObjectCallTest());
		paramBench.add(new ShortCallTest());
		paramBench.add(new StringArrayCallTest());
		paramBench.add(new StringCallTest());
		paramBench.add(new StringUTF8CallTest());
		paramBench.addSeparator();
		
		paramBench.add(new BoolFalseObjectCallTest());
		paramBench.add(new BoolTrueObjectCallTest());
		paramBench.add(new ByteObjectCallTest());
		paramBench.add(new CharObjectCallTest());
		paramBench.add(new DoubleObjectCallTest());
		paramBench.add(new FloatObjectCallTest());
		paramBench.add(new IntObjectCallTest());
		paramBench.add(new LongObjectCallTest());
		paramBench.add(new ShortObjectCallTest());
		paramBench.add(new IntObjectArrayCallTest());
	}
	
	private void initObjectBench() {
		objectBench.add(new ComplexCustomObjectCallTest());
		objectBench.add(new ComplexCustomObjectReturnTest());
		
		objectBench.addSeparator();
		objectBench.add(new ArrayListCallTest());
		objectBench.add(new ArrayListReturnTest());
		objectBench.add(new HashMapCallTest());
		objectBench.add(new HashMapReturnTest());
		objectBench.add(new HashSetCallTest());
		objectBench.add(new HashSetReturnTest());
		
		objectBench.addSeparator();
		objectBench.add(new DateCallTest());
		objectBench.add(new DateReturnTest());
		
		objectBench.addSeparator();
		objectBench.add(new CustomSubObjectCallTest());
		objectBench.add(new CustomSubObjectReturnTest());
	}

}
