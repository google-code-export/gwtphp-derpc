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

import java.util.Collection;
import java.util.HashSet;

import com.google.gwt.core.ext.TreeLogger;
import com.google.gwt.core.ext.UnableToCompleteException;

@SuppressWarnings("serial")
public class PhpTypeFileArtifact extends PhpFileArtifact {
	
	HashSet<PhpType> sources = new HashSet<PhpType>();

	protected PhpTypeFileArtifact(String serviceName, String partialPath) {
		super(serviceName, partialPath);
	}

	@Override
	protected void getContents(TreeLogger logger, StringBuffer buffer)
			throws UnableToCompleteException {
		buffer.append("<?php\n\n");
		for (PhpType source : sources) {
			buffer.append(source.getSource(logger));
			buffer.append("\n");
		}
	}
	
	public void addSource(PhpType src) {
		sources.add(src);
	}
	
	public void addSource(Collection<? extends PhpType> srcs) {
		sources.addAll(srcs);
	}

}
