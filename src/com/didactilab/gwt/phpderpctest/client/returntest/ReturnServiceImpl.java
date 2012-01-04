package com.didactilab.gwt.phpderpctest.client.returntest;

import com.didactilab.gwt.phpderpctest.client.service.CustomEnum;
import com.didactilab.gwt.phpderpctest.client.service.CustomObject;
import com.google.gwt.user.client.rpc.AsyncCallback;

public interface ReturnServiceImpl {

	void boolTrueReturn(AsyncCallback<Boolean> callback);

	void boolFalseReturn(AsyncCallback<Boolean> callback);

	void byteReturn(AsyncCallback<Byte> callback);

	void charReturn(AsyncCallback<Character> callback);

	void charUTF8Return(AsyncCallback<Character> callback);

	void doubleReturn(AsyncCallback<Double> callback);

	void enumReturn(AsyncCallback<CustomEnum> callback);

	void floatReturn(AsyncCallback<Float> callback);

	void intReturn(AsyncCallback<Integer> callback);

	void longReturn(AsyncCallback<Long> callback);

	void noReturn(AsyncCallback<Void> callback);

	void objectReturn(AsyncCallback<CustomObject> callback);

	void shortReturn(AsyncCallback<Short> callback);

	void stringReturn(AsyncCallback<String> callback);

	void stringEscapeReturn(AsyncCallback<String> callback);
	
	void stringUTF8Return(AsyncCallback<String> callback);

	void intArrayReturn(AsyncCallback<int[][]> callback);

	void stringArrayReturn(AsyncCallback<String[]> callback);
	
	void objectsReturn(AsyncCallback<CustomObject[]> callback);
	
	void noObjectsReturn(AsyncCallback<CustomObject[]> callback);

	void throwReturn(AsyncCallback<Void> callback);

}
