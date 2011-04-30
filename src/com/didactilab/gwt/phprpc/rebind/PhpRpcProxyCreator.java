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

import com.didactilab.gwt.phprpc.client.PhpRemoteServiceFilename;
import com.didactilab.gwt.phprpc.client.PhpRemoteServiceRelativePath;
import com.google.gwt.core.ext.typeinfo.JClassType;
import com.google.gwt.rpc.rebind.RpcProxyCreator;
import com.google.gwt.user.client.rpc.RemoteServiceRelativePath;

public class PhpRpcProxyCreator extends RpcProxyCreator {

	public PhpRpcProxyCreator(JClassType type) {
		super(type);
	}

	protected String getRemoteServiceRelativePath() {
		String moduleRelativeURL = getRemoteServiceRelativePath(serviceIntf);
		if (moduleRelativeURL != null) {
			return "\"" + moduleRelativeURL + "\"";
		}
		
		String relativePath = getPhpRemoteServiceRelativePath(serviceIntf);
		if (relativePath == null)
			return null;
		
		return "\"" + getServiceFilename(relativePath, serviceIntf) + "\"";
		//return "\"" + relativePath + "/service" + SERVICE_PHP_SCRIPT + "\"";
		
		
		/*String name = getPhpRemoteServiceName(serviceIntf);
		if (name == null)
			return null;
		else {
			System.out.println("   #service " + name);
			return "\"" + name + "/" + SERVICE_PHP_SCRIPT + "\"";
		}*/
	}
	
	static String getServiceFilename(String relativePath, JClassType type) {
		return relativePath + "/" + type.getSimpleSourceName() + ".service.php";
	}
	
	static String getRemoteServiceRelativePath(JClassType type) {
		RemoteServiceRelativePath moduleRelativeURL = type.getAnnotation(RemoteServiceRelativePath.class);
		if (moduleRelativeURL != null) {
			return moduleRelativeURL.value();
		}
		return null;
	}
	
	static String getPhpRemoteServiceRelativePath(JClassType type) {
		PhpRemoteServiceRelativePath moduleRelativeURL = type.getAnnotation(PhpRemoteServiceRelativePath.class);
		if (moduleRelativeURL != null) {
			return moduleRelativeURL.value();
		}
		return null;
	}
	
	static String getPhpRemoteServiceFilename(JClassType type) {
		PhpRemoteServiceFilename remoteServiceFilename = type.getAnnotation(PhpRemoteServiceFilename.class);
		if (remoteServiceFilename != null) {
			return remoteServiceFilename.value();
		}
		return null;
	}

}
