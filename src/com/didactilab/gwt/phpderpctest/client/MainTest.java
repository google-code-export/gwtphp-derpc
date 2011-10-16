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
import com.didactilab.gwt.phpderpctest.client.objecttest.ArrayListReturnTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.ComplexCustomObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.ComplexCustomObjectReturnTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.CustomSubObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.CustomSubObjectReturnTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.DateCallTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.DateReturnTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.HashMapCallTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.HashMapReturnTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.HashSetCallTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.HashSetReturnTest;
import com.didactilab.gwt.phpderpctest.client.objecttest.ObjectServiceImpl;
import com.didactilab.gwt.phpderpctest.client.paramtest.BoolFalseCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.BoolFalseObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.BoolTrueCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.BoolTrueObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.ByteCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.ByteObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.CharCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.CharObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.CharUTF8CallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.DoubleCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.DoubleObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.EnumCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.FloatCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.FloatObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.IntArrayCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.IntCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.IntObjectArrayCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.IntObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.LongCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.LongObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.ObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.ParamServiceImpl;
import com.didactilab.gwt.phpderpctest.client.paramtest.ShortCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.ShortObjectCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.StringArrayCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.StringCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.StringEscapeCallTest;
import com.didactilab.gwt.phpderpctest.client.paramtest.StringUTF8CallTest;
import com.didactilab.gwt.phpderpctest.client.returntest.BoolFalseReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.BoolTrueReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.ByteReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.CharReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.CharUTF8ReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.DoubleReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.EnumReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.FloatReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.IntArrayReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.IntReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.LongReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.NoReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.ObjectReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.ReturnServiceImpl;
import com.didactilab.gwt.phpderpctest.client.returntest.ShortReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.StringArrayReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.StringEscapeReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.StringReturnTest;
import com.didactilab.gwt.phpderpctest.client.returntest.StringUTF8ReturnTest;
import com.didactilab.gwt.phpderpctest.client.service.ObjectServiceAdapter;
import com.didactilab.gwt.phpderpctest.client.service.ParamServiceAdapter;
import com.didactilab.gwt.phpderpctest.client.service.ReturnServiceAdapter;
import com.didactilab.gwt.phpderpctest.client.service.RpcObjectServiceAdapter;
import com.didactilab.gwt.phpderpctest.client.service.RpcParamServiceAdapter;
import com.didactilab.gwt.phpderpctest.client.service.RpcReturnServiceAdapter;
import com.didactilab.gwt.phpderpctest.client.unittest.TestConnector;
import com.didactilab.gwt.phpderpctest.client.visual.TestBench;
import com.google.gwt.core.client.EntryPoint;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.TabPanel;

public class MainTest implements EntryPoint {
	
	private TabPanel panel = new TabPanel();
	private TestBench returnBench = new TestBench("deRPC Return test");
	private TestBench paramBench = new TestBench("deRPC Param test");
	private TestBench objectBench = new TestBench("deRPC Object test");
	
	private TestBench returnRpcBench = new TestBench("RPC Return test");
	private TestBench paramRpcBench = new TestBench("RPC Param test");
	private TestBench objectRpcBench = new TestBench("RPC Object test");
	
	@Override
	public void onModuleLoad() {
		RootPanel.get().add(panel);
		
		panel.add(returnBench, "deRPC Return test");
		panel.add(paramBench, "deRPC Param test");
		panel.add(objectBench, "deRPC Object test");
		
		panel.add(returnRpcBench, "RPC Return test");
		panel.add(paramRpcBench, "RPC Param test");
		panel.add(objectRpcBench, "RPC Object test");
		
		panel.selectTab(5);
		
		initReturnBench(returnBench, new ReturnServiceAdapter.Connector());
		initParamBench(paramBench, new ParamServiceAdapter.Connector());
		initObjectBench(objectBench, new ObjectServiceAdapter.Connector());
		
		initReturnBench(returnRpcBench, new RpcReturnServiceAdapter.Connector());
		initParamBench(paramRpcBench, new RpcParamServiceAdapter.Connector());
		initObjectBench(objectRpcBench, new RpcObjectServiceAdapter.Connector());
	}
	
	private void initReturnBench(TestBench bench, TestConnector<ReturnServiceImpl> connector) {
		bench.add(new NoReturnTest(connector));
		bench.add(new BoolFalseReturnTest(connector));
		bench.add(new BoolTrueReturnTest(connector));
		bench.add(new ByteReturnTest(connector));
		bench.add(new CharReturnTest(connector));
		bench.add(new CharUTF8ReturnTest(connector));
		bench.add(new DoubleReturnTest(connector));
		bench.add(new FloatReturnTest(connector));
		bench.add(new IntReturnTest(connector));
		bench.add(new LongReturnTest(connector));
		bench.add(new ShortReturnTest(connector));
		bench.add(new StringReturnTest(connector));
		bench.add(new StringEscapeReturnTest(connector));
		bench.add(new StringUTF8ReturnTest(connector));
		bench.add(new ObjectReturnTest(connector));
		bench.add(new EnumReturnTest(connector));
		bench.add(new IntArrayReturnTest(connector));
		bench.add(new StringArrayReturnTest(connector));
	}
	
	private void initParamBench(TestBench bench, TestConnector<ParamServiceImpl> connector) {
		bench.add(new BoolFalseCallTest(connector));
		bench.add(new BoolTrueCallTest(connector));
		bench.add(new ByteCallTest(connector));
		bench.add(new CharCallTest(connector));
		bench.add(new CharUTF8CallTest(connector));
		bench.add(new DoubleCallTest(connector));
		bench.add(new EnumCallTest(connector));
		bench.add(new FloatCallTest(connector));
		bench.add(new IntArrayCallTest(connector));
		bench.add(new IntCallTest(connector));
		bench.add(new LongCallTest(connector));
		bench.add(new ObjectCallTest(connector));
		bench.add(new ShortCallTest(connector));
		bench.add(new StringArrayCallTest(connector));
		bench.add(new StringCallTest(connector));
		bench.add(new StringEscapeCallTest(connector));
		bench.add(new StringUTF8CallTest(connector));
		bench.addSeparator();
		
		bench.add(new BoolFalseObjectCallTest(connector));
		bench.add(new BoolTrueObjectCallTest(connector));
		bench.add(new ByteObjectCallTest(connector));
		bench.add(new CharObjectCallTest(connector));
		bench.add(new DoubleObjectCallTest(connector));
		bench.add(new FloatObjectCallTest(connector));
		bench.add(new IntObjectCallTest(connector));
		bench.add(new LongObjectCallTest(connector));
		bench.add(new ShortObjectCallTest(connector));
		bench.add(new IntObjectArrayCallTest(connector));
	}
	
	private void initObjectBench(TestBench bench, TestConnector<ObjectServiceImpl> connector) {
		bench.add(new ComplexCustomObjectCallTest(connector));
		bench.add(new ComplexCustomObjectReturnTest(connector));
		
		bench.addSeparator();
		bench.add(new ArrayListCallTest(connector));
		bench.add(new ArrayListReturnTest(connector));
		bench.add(new HashMapCallTest(connector));
		bench.add(new HashMapReturnTest(connector));
		bench.add(new HashSetCallTest(connector));
		bench.add(new HashSetReturnTest(connector));
		
		bench.addSeparator();
		bench.add(new DateCallTest(connector));
		bench.add(new DateReturnTest(connector));
		
		bench.addSeparator();
		bench.add(new CustomSubObjectCallTest(connector));
		bench.add(new CustomSubObjectReturnTest(connector));
	}

}
