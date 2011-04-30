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
package com.didactilab.gwt.phprpc.rebind;

import com.google.gwt.core.ext.TreeLogger;
import com.google.gwt.core.ext.UnableToCompleteException;

public abstract class PhpType {
	
	private String name;
	
	public PhpType(String name) {
		this.name = name;
	}
	
	@Override
	public int hashCode() {
		return name.hashCode();
	}
	
	@Override
	public boolean equals(Object o) {
		if (!(o instanceof PhpType))
			return false;
		return name.equals(((PhpType) o).name);
	}

	protected abstract void getContents(TreeLogger logger, StringBuffer buffer) 
		throws UnableToCompleteException;

	
	public String getSource(TreeLogger logger) throws UnableToCompleteException {
		StringBuffer buffer = new StringBuffer();
		getContents(logger, buffer);
		return buffer.toString();
	}

}
