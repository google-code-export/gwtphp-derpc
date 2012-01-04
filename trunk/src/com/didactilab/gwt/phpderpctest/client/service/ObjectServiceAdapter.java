package com.didactilab.gwt.phpderpctest.client.service;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.HashSet;

import com.didactilab.gwt.phpderpctest.client.objecttest.ObjectServiceImpl;
import com.didactilab.gwt.phpderpctest.client.service.CustomObject.CustomSubObject;
import com.didactilab.gwt.phpderpctest.client.unittest.TestConnector;
import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;

public class ObjectServiceAdapter implements ObjectServiceImpl {
	
	public static class Connector implements TestConnector<ObjectServiceImpl> {

		@Override
		public ObjectServiceImpl getConnector() {
			return new ObjectServiceAdapter();
		}
		
	}

	protected ObjectServiceAsync service = GWT.create(ObjectService.class);
	
	@Override
	public void paramCall(ComplexCustomObject param,
			AsyncCallback<Boolean> callback) {
		service.paramCall(param, callback);
	}

	@Override
	public void returnTest(AsyncCallback<ComplexCustomObject> callback) {
		service.returnTest(callback);
	}

	@Override
	public void arrayListCall(ArrayList<Boolean> list,
			AsyncCallback<Boolean> callback) {
		service.arrayListCall(list, callback);
	}

	@Override
	public void arrayListReturn(AsyncCallback<ArrayList<CustomObject>> callback) {
		service.arrayListReturn(callback);
	}

	@Override
	public void hashMapCall(HashMap<String, String> map,
			AsyncCallback<Boolean> callback) {
		service.hashMapCall(map, callback);
	}

	@Override
	public void hashMapReturn(AsyncCallback<HashMap<Integer, String>> callback) {
		service.hashMapReturn(callback);
	}

	@Override
	public void hashSetReturn(AsyncCallback<HashSet<String>> callback) {
		service.hashSetReturn(callback);
	}

	@Override
	public void hashSetCall(HashSet<Integer> set,
			AsyncCallback<Boolean> callback) {
		service.hashSetCall(set, callback);
	}

	@Override
	public void dateCall(Date date, AsyncCallback<Boolean> callback) {
		service.dateCall(date, callback);
	}

	@Override
	public void dateReturn(AsyncCallback<Date> callback) {
		service.dateReturn(callback);
	}

	@Override
	public void customSubObjectCall(CustomSubObject obj,
			AsyncCallback<Boolean> callback) {
		service.customSubObjectCall(obj, callback);
	}

	@Override
	public void customSubObjectReturn(AsyncCallback<CustomSubObject> callback) {
		service.customSubObjectReturn(callback);
	}

	@Override
	public void arrayObjectEmptyReturn(AsyncCallback<CustomArrayObject> callback) {
		service.arrayObjectEmptyReturn(callback);
	}

}
