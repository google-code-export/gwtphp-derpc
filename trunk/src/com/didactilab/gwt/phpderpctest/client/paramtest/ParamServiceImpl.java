package com.didactilab.gwt.phpderpctest.client.paramtest;

import com.didactilab.gwt.phpderpctest.client.service.CustomEnum;
import com.didactilab.gwt.phpderpctest.client.service.CustomObject;
import com.google.gwt.user.client.rpc.AsyncCallback;

public interface ParamServiceImpl {

	void boolFalseCall(boolean param, AsyncCallback<Boolean> callback);

	void boolTrueCall(boolean param, AsyncCallback<Boolean> callback);

	void byteCall(byte param, AsyncCallback<Boolean> callback);

	void charCall(char param, AsyncCallback<Boolean> callback);

	void charUTF8Call(char param, AsyncCallback<Boolean> callback);

	void doubleCall(double param, AsyncCallback<Boolean> callback);

	void enumCall(CustomEnum param, AsyncCallback<Boolean> callback);

	void floatCall(float param, AsyncCallback<Boolean> callback);

	void intArrayCall(int[][] param, AsyncCallback<Boolean> callback);

	void intCall(int param, AsyncCallback<Boolean> callback);

	void longCall(long param, AsyncCallback<Boolean> callback);

	void objectCall(CustomObject param, AsyncCallback<Boolean> callback);

	void shortCall(short param, AsyncCallback<Boolean> callback);

	void stringArrayCall(String[] param, AsyncCallback<Boolean> callback);

	void stringCall(String param, AsyncCallback<Boolean> callback);

	void stringUTF8Call(String param, AsyncCallback<Boolean> callback);

	void intObjectCall(Integer param, AsyncCallback<Boolean> callback);

	void boolFalseObjectCall(Boolean param, AsyncCallback<Boolean> callback);

	void boolTrueObjectCall(Boolean param, AsyncCallback<Boolean> callback);

	void charObjectCall(Character param, AsyncCallback<Boolean> callback);

	void shortObjectCall(Short param, AsyncCallback<Boolean> callback);

	void longObjectCall(Long param, AsyncCallback<Boolean> callback);

	void floatObjectCall(Float param, AsyncCallback<Boolean> callback);

	void doubleObjectCall(Double param, AsyncCallback<Boolean> callback);

	void byteObjectCall(Byte param, AsyncCallback<Boolean> callback);
	
	void intObjectArrayCall(Integer[][] param, AsyncCallback<Boolean> callback);
	
}
