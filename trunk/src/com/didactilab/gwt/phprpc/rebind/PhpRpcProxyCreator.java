package com.didactilab.gwt.phprpc.rebind;

import com.didactilab.gwt.phprpc.client.PhpRemoteServiceFilename;
import com.didactilab.gwt.phprpc.client.PhpRemoteServiceRelativePath;
import com.google.gwt.core.ext.typeinfo.JClassType;
import com.google.gwt.user.client.rpc.RemoteServiceRelativePath;
import com.google.gwt.user.rebind.rpc.ProxyCreator;

public class PhpRpcProxyCreator extends ProxyCreator {

	public PhpRpcProxyCreator(JClassType serviceIntf) {
		super(serviceIntf);
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
