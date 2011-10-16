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

import com.didactilab.gwt.phprpc.client.ConvertToPhp;
import com.google.gwt.user.client.rpc.IsSerializable;

@ConvertToPhp
public class ComplexCustomObject implements IsSerializable {

	public String string;
	public int number;
	public boolean bool;
	public double real;
	public long big;
	public CustomEnum custom;
	public byte[] bytes;
	public String escapedString;
	
	public boolean isValid() {
		return (string != null && string.equals("salut")) &&
				(number == 5) &&
				(bool) &&
				(real == 560.3345) &&
				(big == 6000000000l) &&
				(custom == CustomEnum.HELLO) &&
				(bytes[0] == 1) &&
				(bytes[1] == 2) &&
				(bytes[2] == 3) &&
				(escapedString != null && escapedString.equals("salut\nhello\tbonjour"));
	}
	
	public void fill() {
		string = "salut";
		number = 5;
		bool = true;
		real = 560.3345;
		big = 6000000000l;
		custom = CustomEnum.HELLO;
		bytes = new byte[] {1, 2, 3};
		escapedString = "salut\nhello\tbonjour";
	}
	
	@Override
	public String toString() {
		return "[ComplexCustomObject number=" + number + " string=\"" + string + 
				"\" bool=" + bool + " real=" + real + " big=" + big + " custom=" + custom.toString() +
				" bytes=[" + bytes[0] + " " + bytes[1] + " " + bytes[2] + "] escapedString=" + escapedString + "]";
	}
	
}
