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
package com.didactilab.gwt.phpderpctest.client.unittest;

public abstract class TestCase {

	private TestStatus status = TestStatus.NOT_STARTED;
	private TestCaseHandler handler;
	private boolean autoPass = true;
	
	public void run() {
		changeStatus(TestStatus.EXECUTING);
		try {
			execute();
		}
		catch (TestCaseException e) {
			changeStatus(TestStatus.FAILED);
			doFail(e.getFailedMessage());
			return;
		}
		catch (Exception e) {
			changeStatus(TestStatus.FAILED);
			doFail(e.toString());
			return;
		}
		if (autoPass) {
			changeStatus(TestStatus.PASSED);
			doPass();
		}
	}
	
	protected abstract void execute();
	
	protected void asyncPass() {
		changeStatus(TestStatus.PASSED);
		doPass();
	}
	
	protected void asyncFail() {
		changeStatus(TestStatus.FAILED);
		doFail(null);
	}
	
	protected void asyncFail(String message) {
		changeStatus(TestStatus.FAILED);
		doFail(message);
	}
	
	protected void asyncWait() {
		autoPass = false;
	}
	
	private void changeStatus(TestStatus newStatus) {
		status = newStatus;
	}
	
	private void doFail(String message) {
		if (handler != null)
			handler.failed(this, message);
	}
	
	private void doPass() {
		if (handler != null)
			handler.passed(this);
	}
	
	public abstract String getTitle();
	
	public TestStatus getStatus() {
		return status;
	}
	
	public void setHandler(TestCaseHandler handler) {
		this.handler = handler;
	}
	
	public TestCaseHandler getHandler() {
		return handler;
	}
	
	protected void fail() {
		throw new TestCaseException();
	}
	
	protected void fail(String message) {
		throw new TestCaseException(message);
	}
	
	protected void assertEquals(boolean expected, boolean actual) {
		if (expected != actual)
			fail();
	}
	
	protected void assertEquals(boolean expected, boolean actual, String message) {
		if (expected != actual)
			fail(message);
	}
	
	protected void assertFalse(boolean condition) {
		if (condition)
			fail();
	}
	
	protected void assertFalse(boolean condition, String message) {
		if (condition)
			fail(message);
	}
	
	protected void assertTrue(boolean condition) {
		if (!condition)
			fail();
	}
	
	protected void assertTrue(boolean condition, String message) {
		if (!condition)
			fail(message);
	}
	
	protected void asyncFailIfNot(boolean condition) {
		if (!condition)
			asyncFail();
		else 
			asyncPass();
	}
	
	protected void asyncFailIfNot(boolean condition, String message) {
		if (!condition)
			asyncFail(message);
		else
			asyncPass();
	}
	
	protected void asyncFailIf(boolean condition) {
		if (condition)
			asyncFail();
		else
			asyncPass();
	}
	
	protected void asyncFailIf(boolean condition, String message) {
		if (condition)
			asyncFail(message);
		else
			asyncPass();
	}
	
	protected void asyncPassIfNot(boolean condition) {
		if (!condition)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassIfNot(boolean condition, String message) {
		if (!condition)
			asyncPass();
		else
			asyncFail(message);
	}
	
	protected void asyncPassIf(boolean condition) {
		if (condition)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassIf(boolean condition, String message) {
		if (condition)
			asyncPass();
		else
			asyncFail(message);
	}
	
	protected void asyncPassEquals(byte expected, byte actual) {
		if (expected == actual)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassEquals(char expected, char actual) {
		if (expected == actual)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassEquals(int expected, int actual) {
		if (expected == actual)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassEquals(long expected, long actual) {
		if (expected == actual)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassEquals(short expected, short actual) {
		if (expected == actual)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassEquals(double expected, double actual) {
		if (expected == actual)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassEquals(float expected, float actual) {
		if (expected == actual)
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassEquals(String expected, String actual) {
		if (expected.equals(actual))
			asyncPass();
		else
			asyncFail();
	}
	
	protected void asyncPassEqualsObject(Object expected, Object actual) {
		if (expected.equals(actual))
			asyncPass();
		else
			asyncFail();
	}
}
