package com.didactilab.gwt.phpderpctest.client.objecttest;

import com.didactilab.gwt.phpderpctest.client.service.CustomArrayObject;
import com.didactilab.gwt.phpderpctest.client.unittest.TestConnector;
import com.google.gwt.user.client.rpc.AsyncCallback;

public class ArrayObjectEmptyReturnTest extends ObjectTestCase {

	public ArrayObjectEmptyReturnTest(TestConnector<ObjectServiceImpl> connector) {
		super(connector);
	}

	@Override
	protected void execute() {
		service.arrayObjectEmptyReturn(new AsyncCallback<CustomArrayObject> () {
			@Override
			public void onFailure(Throwable caught) {
				asyncFail("Failure \n" + caught);
			}

			@Override
			public void onSuccess(CustomArrayObject result) {
				boolean res = result.objects != null && result.objects.length == 0;
				assertTrue(res);
			}
			
		});
	}

	@Override
	public String getTitle() {
		return "Return ArrayObject(empty array)";
	}

}
