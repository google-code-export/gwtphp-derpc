package com.didactilab.gwt.phprpc.rebind;

import com.google.gwt.core.ext.GeneratorContextExt;
import com.google.gwt.core.ext.TreeLogger;
import com.google.gwt.core.ext.UnableToCompleteException;
import com.google.gwt.core.ext.typeinfo.JClassType;
import com.google.gwt.dev.javac.rebind.RebindResult;
import com.google.gwt.user.rebind.rpc.ProxyCreator;
import com.google.gwt.user.rebind.rpc.ServiceInterfaceProxyGenerator;

public class PhpRpcServiceGenerator extends ServiceInterfaceProxyGenerator {

	@Override
	public RebindResult generateIncrementally(TreeLogger logger,
			GeneratorContextExt context, String typeName)
			throws UnableToCompleteException {

		PhpFilesGenerator generator = new PhpFilesGenerator.Rpc(logger, context, typeName);
		generator.generate();

		return super.generateIncrementally(logger, context, typeName);
	}

	@Override
	protected ProxyCreator createProxyCreator(JClassType remoteService) {
		return new PhpRpcProxyCreator(remoteService);
	}
	
}
