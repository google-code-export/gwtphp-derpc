package com.didactilab.gwt.phpderpctest.client.paramtest;

import com.didactilab.gwt.phpderpctest.client.unittest.TestConnector;
import com.google.gwt.user.client.rpc.AsyncCallback;

public class StringEscapeCallTest extends ParamTestCase {

	public StringEscapeCallTest(TestConnector<ParamServiceImpl> connector) {
		super(connector);
	}

	@Override
	protected void execute() {
		asyncWait();
		service.stringEscapeCall("Hello\nSalut\nGuten tag\tGood morning", new AsyncCallback<Boolean>() {
			@Override
			public void onSuccess(Boolean result) {
				asyncPassIf(result);
			}
			
			@Override
			public void onFailure(Throwable caught) {
				asyncFail("Failure \n" + caught);
			}
		});
	}

	@Override
	public String getTitle() {
		return "Call escaped String (\"Hello\\nSalut\\nGuten tag\\tGood morning\")";
	}

}
