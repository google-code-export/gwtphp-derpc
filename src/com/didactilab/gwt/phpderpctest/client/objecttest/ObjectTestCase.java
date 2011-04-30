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

import com.didactilab.gwt.phpderpctest.client.service.ObjectService;
import com.didactilab.gwt.phpderpctest.client.service.ObjectServiceAsync;
import com.didactilab.gwt.phpderpctest.client.unittest.TestCase;
import com.google.gwt.core.client.GWT;

public abstract class ObjectTestCase extends TestCase {

	protected static ObjectServiceAsync service = GWT.create(ObjectService.class);

}
