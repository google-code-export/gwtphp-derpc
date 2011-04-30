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
package com.didactilab.gwt.phprpc.linker;

import java.io.IOException;
import java.io.OutputStream;

import com.google.gwt.user.client.rpc.SerializationException;

public abstract class WebClientOracleWriter {

	private OutputStream out;
	
	public WebClientOracleWriter(OutputStream out) {
		this.out = out;
	}
	
	public void write(String data) throws SerializationException {
		try {
			byte[] bytes = data.getBytes("UTF-8");
			out.write(bytes);
		} catch (IOException e) {
			throw new SerializationException(e.getMessage(), e);
		}
	}
	
	public void begin() throws SerializationException {
	}
	
	public void finish() throws SerializationException {
	}
	
	public void writeObject(Object obj) throws SerializationException {
		writeObject(obj, null);
	}
	
	public abstract void writeObject(Object instance, String renameClass) throws SerializationException;
	
}
