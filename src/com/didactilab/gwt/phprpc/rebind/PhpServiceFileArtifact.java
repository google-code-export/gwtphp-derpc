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

import java.util.HashSet;
import java.util.List;
import java.util.TreeSet;

import com.didactilab.gwt.phprpc.tools.StringTools;
import com.google.gwt.core.ext.TreeLogger;
import com.google.gwt.core.ext.UnableToCompleteException;

@SuppressWarnings("serial")
public abstract class PhpServiceFileArtifact extends PhpFileArtifact {
	
	public static class Rpc extends PhpServiceFileArtifact {

		public Rpc(String serviceName,
				String partialPath, String serviceFilename) {
			super(serviceName, partialPath, serviceFilename);
		}

		@Override
		protected String getServletFile() {
			return "rpc/RemoteServiceServlet.php";
		}

		@Override
		protected String getServletType() {
			return "RemoteServiceServlet";
		}

		@Override
		protected String getServletParam() {
			return "new " + getServiceName() + "()";
		}
		
	}
	
	public static class DeRpc extends PhpServiceFileArtifact {

		public DeRpc(String serviceName,
				String partialPath, String serviceFilename) {
			super(serviceName, partialPath, serviceFilename);
		}

		@Override
		protected String getServletFile() {
			return "derpc/RpcServlet.php";
		}

		@Override
		protected String getServletType() {
			return "RpcServlet";
		}

		@Override
		protected String getServletParam() {
			return "";
		}
		
	}
	
	private final String serviceFilename;
	private HashSet<String> includes = new HashSet<String>();
	private TreeSet<String> includePaths = new TreeSet<String>();
	private String moduleRelativePath;
	
	public PhpServiceFileArtifact(String serviceName, String partialPath, String serviceFilename) {
		super(serviceName, partialPath);
		this.serviceFilename = serviceFilename;
		//
		int subPathCount = StringTools.charCount(getPartialPath(), '/');
		moduleRelativePath = StringTools.repeat("../", subPathCount);
	}
	
	public void add(String includedFile) {
		includes.add(includedFile);
	}

	@Override
	protected void getContents(TreeLogger logger, StringBuffer buffer)
			throws UnableToCompleteException {
		
		buffer.append("<?php\n");
		buffer.append("/*\n");
		buffer.append(" * Copyright 2011 DidactiLab SAS\n");
		buffer.append(" * \n");
		buffer.append(" * Licensed under the Apache License, Version 2.0 (the \"License\"); you may not\n");
		buffer.append(" * use this file except in compliance with the License. You may obtain a copy of\n");
		buffer.append(" * the License at\n");
		buffer.append(" * \n");
		buffer.append(" * http://www.apache.org/licenses/LICENSE-2.0\n");
		buffer.append(" * \n");
		buffer.append(" * Unless required by applicable law or agreed to in writing, software\n");
		buffer.append(" * distributed under the License is distributed on an \"AS IS\" BASIS, WITHOUT\n");
		buffer.append(" * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the\n");
		buffer.append(" * License for the specific language governing permissions and limitations under\n");
		buffer.append(" * the License.\n");
		buffer.append(" * \n");
		buffer.append(" * Date: 30 avr. 2011\n");
		buffer.append(" * Author: Mathieu LIGOCKI\n");
		buffer.append(" * Auto-generated file\n");
		buffer.append(" */\n\n");
		
		buffer.append("const GWT_MODULE_BASE_PATH = '").append(moduleRelativePath).append("';\n");
		buffer.append("const PHPRPC_ROOT = '").append(moduleRelativePath).append("phprpc/").append("';\n");
		buffer.append('\n');
		
		if (!includePaths.isEmpty()) {
			buffer.append("set_include_path(get_include_path()");
			for (String path : includePaths) {
				buffer.append(" . PATH_SEPARATOR . '").append(path).append("'");
			}
			buffer.append(");\n\n");
		}
		
		buffer.append("require_once PHPRPC_ROOT . '" + getServletFile() + "';\n\n");
		buffer.append("require_once '").append(serviceFilename).append("';\n");
		
		for (String includedFile : includes) {
			buffer.append("require_once '").append(includedFile).append("';\n");
		}
		
		buffer.append("\n");
		buffer.append(getServletType() + "::run(" + getServletParam() + ");");
		
	}
	
	public void setIncludePaths(List<String> paths) {
		for (String path : paths) {
			includePaths.add(moduleRelativePath + path);
		}
	}

	protected abstract String getServletFile();
	protected abstract String getServletType();
	protected abstract String getServletParam();
	
}
