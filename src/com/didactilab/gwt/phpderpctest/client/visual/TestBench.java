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
package com.didactilab.gwt.phpderpctest.client.visual;

import java.util.HashMap;

import com.didactilab.gwt.phpderpctest.client.unittest.TestCase;
import com.didactilab.gwt.phpderpctest.client.unittest.TestCaseHandler;
import com.didactilab.gwt.phpderpctest.client.unittest.TestStatus;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.Grid;
import com.google.gwt.user.client.ui.VerticalPanel;

public class TestBench extends Composite {

	private Grid table = new Grid(0, 4);
	private Button startBtn = new Button("Run all");
	
	private HashMap<TestCase, Integer> tests = new HashMap<TestCase, Integer>();
	
	private TestCaseHandler handler = new TestCaseHandler() {
		@Override
		public void failed(TestCase test, String message) {
			updateTest(test, message);
		}

		@Override
		public void passed(TestCase test) {
			updateTest(test, "");
		}
	};
	
	public TestBench(String title) {
		super();
		
		VerticalPanel panel = new VerticalPanel();
		panel.add(startBtn);
		panel.add(table);
		
		initWidget(panel);
		
		startBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				for (TestCase test : tests.keySet())
					startTest(test);
			}
		});
	}
	
	public void add(final TestCase test) {
		int newIndex = table.getRowCount();
		table.resizeRows(newIndex + 1);
		//
		Button btn = new Button("run");
		btn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				startTest(test);
			}
		});
		//
		table.setWidget(newIndex, 0, btn);
		table.setText(newIndex, 1, test.getTitle());
		table.setText(newIndex, 2, test.getStatus().toString());
		table.setText(newIndex, 3, "");
		//
		tests.put(test, newIndex);
		test.setHandler(handler);
	}
	
	public void addSeparator() {
		int newIndex = table.getRowCount();
		table.resizeRows(newIndex + 1);
	}
	
	private void updateTest(TestCase test, String message) {
		int index = tests.get(test);
		TestStatus status = test.getStatus();
		table.setText(index, 2, status.toString());
		table.setText(index, 3, message);
		
		String color = "";
		switch (status) {
			case EXECUTING: color = "orange"; break;
			case FAILED: color = "red"; break;
			case PASSED: color = "green"; break;
			case NOT_STARTED: color = "white"; break;
		}
		table.getCellFormatter().getElement(index, 2).getStyle().setBackgroundColor(color);
	}
	
	private void startTest(TestCase test) {
		test.run();
		updateTest(test, "");
	}
	
}
