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

import com.google.gwt.core.ext.GeneratorContextExt;
import com.google.gwt.core.ext.TreeLogger;
import com.google.gwt.core.ext.UnableToCompleteException;
import com.google.gwt.core.ext.typeinfo.JClassType;
import com.google.gwt.dev.javac.rebind.RebindResult;
import com.google.gwt.rpc.rebind.RpcServiceGenerator;
import com.google.gwt.user.rebind.rpc.ProxyCreator;

public class PhpDerpcServiceGenerator extends RpcServiceGenerator {

	@Override
	public RebindResult generateIncrementally(TreeLogger logger,
			GeneratorContextExt context, String typeName)
			throws UnableToCompleteException {

		PhpFilesGenerator generator = new PhpFilesGenerator.DeRpc(logger, context, typeName);
		generator.generate();

		return super.generateIncrementally(logger, context, typeName);
	}

	@Override
	protected ProxyCreator createProxyCreator(JClassType remoteService) {
		return new PhpDerpcProxyCreator(remoteService);
	}

}
