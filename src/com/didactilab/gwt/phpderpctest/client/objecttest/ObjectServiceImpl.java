package com.didactilab.gwt.phpderpctest.client.objecttest;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.HashSet;

import com.didactilab.gwt.phpderpctest.client.service.CustomArrayObject;
import com.didactilab.gwt.phpderpctest.client.service.ComplexCustomObject;
import com.didactilab.gwt.phpderpctest.client.service.CustomObject;
import com.didactilab.gwt.phpderpctest.client.service.CustomObject.CustomSubObject;
import com.google.gwt.user.client.rpc.AsyncCallback;

public interface ObjectServiceImpl {

	void paramCall(ComplexCustomObject param, AsyncCallback<Boolean> callback);

	void returnTest(AsyncCallback<ComplexCustomObject> callback);

	void arrayListCall(ArrayList<Boolean> list, AsyncCallback<Boolean> callback);
	
	void arrayListReturn(AsyncCallback<ArrayList<CustomObject>> callback);

	void hashMapCall(HashMap<String, String> map, AsyncCallback<Boolean> callback);

	void hashMapReturn(AsyncCallback<HashMap<Integer, String>> callback);

	void hashSetReturn(AsyncCallback<HashSet<String>> callback);

	void hashSetCall(HashSet<Integer> set, AsyncCallback<Boolean> callback);

	void dateCall(Date date, AsyncCallback<Boolean> callback);
	
	void dateReturn(AsyncCallback<Date> callback);
	
	void customSubObjectCall(CustomSubObject obj, AsyncCallback<Boolean> callback);
	
	void customSubObjectReturn(AsyncCallback<CustomSubObject> callback);
	
	void arrayObjectEmptyReturn(AsyncCallback<CustomArrayObject> callback);
	
}
