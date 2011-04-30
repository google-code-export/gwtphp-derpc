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

import java.io.ByteArrayInputStream;
import java.io.InputStream;
import java.io.UnsupportedEncodingException;

import com.didactilab.gwt.phprpc.linker.PhpClientOracleLinker;
import com.google.gwt.core.ext.TreeLogger;
import com.google.gwt.core.ext.UnableToCompleteException;
import com.google.gwt.core.ext.linker.EmittedArtifact;

@SuppressWarnings("serial")
public abstract class PhpFileArtifact extends EmittedArtifact {

	private final String serviceName;
	
	protected PhpFileArtifact(String serviceName, String partialPath) {
		super(PhpClientOracleLinker.class, partialPath);
		this.serviceName = serviceName;
	}
	
	public String getServiceName() {
		return serviceName;
	}

	@Override
	public InputStream getContents(TreeLogger logger) throws UnableToCompleteException {
		StringBuffer buffer = new StringBuffer();
		
		getContents(logger, buffer);
		
		try {
			byte[] bytes = buffer.toString().getBytes("UTF-8");
			return new ByteArrayInputStream(bytes);
		} catch (UnsupportedEncodingException e) {
			logger.log(TreeLogger.ERROR, "Convert string to UTF-8 is not supported (not possible)");
			throw new UnableToCompleteException();
		}
	}
	
	protected abstract void getContents(TreeLogger logger, StringBuffer buffer) 
		throws UnableToCompleteException;
	
}
