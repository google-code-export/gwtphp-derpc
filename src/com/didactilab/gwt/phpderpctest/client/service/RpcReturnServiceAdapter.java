package com.didactilab.gwt.phpderpctest.client.service;

import com.didactilab.gwt.phpderpctest.client.returntest.ReturnServiceImpl;
import com.didactilab.gwt.phpderpctest.client.unittest.TestConnector;
import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;

public class RpcReturnServiceAdapter implements ReturnServiceImpl {

	public static class Connector implements TestConnector<ReturnServiceImpl> {

		@Override
		public ReturnServiceImpl getConnector() {
			return new RpcReturnServiceAdapter();
		}
		
	}
	
	private static RpcReturnServiceAsync service = GWT.create(RpcReturnService.class);
	
	@Override
	public void boolTrueReturn(AsyncCallback<Boolean> callback) {
		service.boolTrueReturn(callback);
	}

	@Override
	public void boolFalseReturn(AsyncCallback<Boolean> callback) {
		service.boolFalseReturn(callback);
	}

	@Override
	public void byteReturn(AsyncCallback<Byte> callback) {
		service.byteReturn(callback);
	}

	@Override
	public void charReturn(AsyncCallback<Character> callback) {
		service.charReturn(callback);
	}

	@Override
	public void charUTF8Return(AsyncCallback<Character> callback) {
		service.charUTF8Return(callback);
	}

	@Override
	public void doubleReturn(AsyncCallback<Double> callback) {
		service.doubleReturn(callback);
	}

	@Override
	public void enumReturn(AsyncCallback<CustomEnum> callback) {
		service.enumReturn(callback);
	}

	@Override
	public void floatReturn(AsyncCallback<Float> callback) {
		service.floatReturn(callback);
	}

	@Override
	public void intReturn(AsyncCallback<Integer> callback) {
		service.intReturn(callback);
	}

	@Override
	public void longReturn(AsyncCallback<Long> callback) {
		service.longReturn(callback);
	}

	@Override
	public void noReturn(AsyncCallback<Void> callback) {
		service.noReturn(callback);
	}

	@Override
	public void objectReturn(AsyncCallback<CustomObject> callback) {
		service.objectReturn(callback);
	}

	@Override
	public void shortReturn(AsyncCallback<Short> callback) {
		service.shortReturn(callback);
	}

	@Override
	public void stringReturn(AsyncCallback<String> callback) {
		service.stringReturn(callback);
	}
	
	@Override
	public void stringEscapeReturn(AsyncCallback<String> callback) {
		service.stringEscapeReturn(callback);	
	}

	@Override
	public void stringUTF8Return(AsyncCallback<String> callback) {
		service.stringUTF8Return(callback);
	}

	@Override
	public void intArrayReturn(AsyncCallback<int[][]> callback) {
		service.intArrayReturn(callback);
	}

	@Override
	public void stringArrayReturn(AsyncCallback<String[]> callback) {
		service.stringArrayReturn(callback);
	}

	@Override
	public void throwReturn(AsyncCallback<Void> callback) {
		service.throwReturn(callback);
	}

	@Override
	public void objectsReturn(AsyncCallback<CustomObject[]> callback) {
		service.objectsReturn(callback);
	}

	@Override
	public void noObjectsReturn(AsyncCallback<CustomObject[]> callback) {
		service.noObjectsReturn(callback);
	}

}
