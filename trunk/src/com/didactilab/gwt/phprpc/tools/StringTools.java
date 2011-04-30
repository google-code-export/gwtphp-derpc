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
package com.didactilab.gwt.phprpc.tools;

public class StringTools {

	public static int charCount(String str, char searched) {
		int count = 0;
		for (int i = 0, c = str.length(); i < c; i++) {
			if (str.charAt(i) == searched)
				count++;
		}
		return count;
	}
	
	public static String repeat(String str, int count) {
		String res = "";
		for (int i = 0; i < count; i++)
			res += str;
		return res;
	}
	
}
