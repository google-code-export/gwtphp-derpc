<?php
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
 * Date: 17 juil. 2011
 * Author: Mathieu LIGOCKI
 */

require_once PHPRPC_ROOT . 'collections.php';

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.Date_CustomFieldSerializer */
final class Date_CustomFieldSerializer {

	public static function instanciate(SerializationStreamReader $streamReader) {
		return new Date($streamReader->readLong());
	}

	public static function deserialize(SerializationStreamReader $streamReader, $instance) {
		// Nothing
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeLong($instance->getValue());
	}

}