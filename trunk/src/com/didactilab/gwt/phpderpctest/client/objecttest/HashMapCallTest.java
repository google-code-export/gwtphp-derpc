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
package com.didactilab.gwt.phpderpctest.client.objecttest;

import java.util.HashMap;

import com.google.gwt.user.client.rpc.AsyncCallback;

public class HashMapCallTest extends ObjectTestCase {

	@Override
	protected void execute() {
		asyncWait();
		HashMap<String, String> map = new HashMap<String, String>();
		map.put("hello", "salut");
		map.put("yes", "oui");
		map.put("no", "non");
		map.put("house", "maison");
		service.hashMapCall(map, new AsyncCallback<Boolean>() {
			@Override
			public void onFailure(Throwable caught) {
				asyncFail("Failure \n" + caught);
			}

			@Override
			public void onSuccess(Boolean result) {
				asyncPassIf(result);
			}
		});
	}

	@Override
	public String getTitle() {
		return "Call HashMap<String, String>(\"hello\" => \"salut\", \"yes\" => \"oui\"...)";
	}

}
