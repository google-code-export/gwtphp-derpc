package com.didactilab.gwt.phpderpctest.client.service;

import com.didactilab.gwt.phpderpctest.client.paramtest.ParamServiceImpl;
import com.didactilab.gwt.phpderpctest.client.unittest.TestConnector;
import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;

public class RpcParamServiceAdapter implements ParamServiceImpl {
	
	public static class Connector implements TestConnector<ParamServiceImpl> {

		private RpcParamServiceAdapter adapter = new RpcParamServiceAdapter();
		
		@Override
		public ParamServiceImpl getConnector() {
			return adapter;
		}
		
	}

	protected RpcParamServiceAsync service = GWT.create(RpcParamService.class);
	
	@Override
	public void boolFalseCall(boolean param, AsyncCallback<Boolean> callback) {
		service.boolFalseCall(param, callback);
	}

	@Override
	public void boolTrueCall(boolean param, AsyncCallback<Boolean> callback) {
		service.boolTrueCall(param, callback);
	}

	@Override
	public void byteCall(byte param, AsyncCallback<Boolean> callback) {
		service.byteCall(param, callback);
	}

	@Override
	public void charCall(char param, AsyncCallback<Boolean> callback) {
		service.charCall(param, callback);
	}

	@Override
	public void charUTF8Call(char param, AsyncCallback<Boolean> callback) {
		service.charUTF8Call(param, callback);
	}

	@Override
	public void doubleCall(double param, AsyncCallback<Boolean> callback) {
		service.doubleCall(param, callback);
	}

	@Override
	public void enumCall(CustomEnum param, AsyncCallback<Boolean> callback) {
		service.enumCall(param, callback);
	}

	@Override
	public void floatCall(float param, AsyncCallback<Boolean> callback) {
		service.floatCall(param, callback);
	}

	@Override
	public void intArrayCall(int[][] param, AsyncCallback<Boolean> callback) {
		service.intArrayCall(param, callback);
	}

	@Override
	public void intCall(int param, AsyncCallback<Boolean> callback) {
		service.intCall(param, callback);
	}

	@Override
	public void longCall(long param, AsyncCallback<Boolean> callback) {
		service.longCall(param, callback);
	}

	@Override
	public void objectCall(CustomObject param, AsyncCallback<Boolean> callback) {
		service.objectCall(param, callback);
	}

	@Override
	public void shortCall(short param, AsyncCallback<Boolean> callback) {
		service.shortCall(param, callback);
	}

	@Override
	public void stringArrayCall(String[] param, AsyncCallback<Boolean> callback) {
		service.stringArrayCall(param, callback);
	}

	@Override
	public void stringCall(String param, AsyncCallback<Boolean> callback) {
		service.stringCall(param, callback);
	}
	
	@Override
	public void stringEscapeCall(String param, AsyncCallback<Boolean> callback) {
		service.stringEscapeCall(param, callback);
	}

	@Override
	public void stringUTF8Call(String param, AsyncCallback<Boolean> callback) {
		service.stringUTF8Call(param, callback);
	}

	@Override
	public void intObjectCall(Integer param, AsyncCallback<Boolean> callback) {
		service.intObjectCall(param, callback);
	}

	@Override
	public void boolFalseObjectCall(Boolean param,
			AsyncCallback<Boolean> callback) {
		service.boolFalseObjectCall(param, callback);
	}

	@Override
	public void boolTrueObjectCall(Boolean param,
			AsyncCallback<Boolean> callback) {
		service.boolTrueObjectCall(param, callback);
	}

	@Override
	public void charObjectCall(Character param, AsyncCallback<Boolean> callback) {
		service.charObjectCall(param, callback);
	}

	@Override
	public void shortObjectCall(Short param, AsyncCallback<Boolean> callback) {
		service.shortObjectCall(param, callback);
	}

	@Override
	public void longObjectCall(Long param, AsyncCallback<Boolean> callback) {
		service.longObjectCall(param, callback);
	}

	@Override
	public void floatObjectCall(Float param, AsyncCallback<Boolean> callback) {
		service.floatObjectCall(param, callback);
	}

	@Override
	public void doubleObjectCall(Double param, AsyncCallback<Boolean> callback) {
		service.doubleObjectCall(param, callback);
	}

	@Override
	public void byteObjectCall(Byte param, AsyncCallback<Boolean> callback) {
		service.byteObjectCall(param, callback);
	}

	@Override
	public void intObjectArrayCall(Integer[][] param,
			AsyncCallback<Boolean> callback) {
		service.intObjectArrayCall(param, callback);
	}

}
