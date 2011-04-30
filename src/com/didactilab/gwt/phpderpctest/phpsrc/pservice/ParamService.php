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
 * Date: 30 avr. 2011
 * Author: Mathieu LIGOCKI
 */

require_once 'common.php';

/** @gwtname com.didactilab.gwt.phprpctest.client.service.ParamService */
class ParamService implements RemoteService {

	/** @return boolean */
	public function boolTrueCall($param) {
		return ($param == true);
	}
	
	/** @return boolean */
	public function boolFalseCall($param) {
		return ($param == false);
	}
	
	/** @return boolean */
	public function byteCall($param) {
		return ($param == 120);
	}
	
	/** @return boolean */
	public function charCall($param) {
		return ($param == 'c');
	}
	
	/** @return boolean */
	public function charUTF8Call($param) {
		return ($param == 'à');
	}
	
	/** @return boolean */
	public function doubleCall($param) {
		return ($param == 5.5);
	}
	
	/** @return boolean */
	public function floatCall($param) {
		return ($param == 6.6);
	}
	
	/** @return boolean */
	public function intCall($param) {
		return ($param == 1000000000);
	}
	
	/** @return boolean */
	public function longCall($param) {
		return ($param == 5000000000);
	}
	
	/** @return boolean */
	public function shortCall($param) {
		return ($param == 32000);
	}
	
	/** @return boolean */
	public function stringCall($param) {
		return ($param == 'Hello the world');
	}
	
	/** @return boolean */
	public function stringUTF8Call($param) {
		return ($param == 'abc éèçàùîö');
	}
	
	/** @return boolean */
	public function objectCall(CustomObject $param) {
		if (Classes::classOf($param) != Classes::classOf(CustomObject))
			return false;
		return ($param->number == 5);
	}
	
	/** @return boolean */
	public function enumCall($param) {
		return ($param == CustomEnum::GUTEN_TAG);
	}
	
	/** @return boolean */
	public function intArrayCall($param) {
		if (!is_array($param)) {
			return false;
		}
		if ($param[0][0] != 1) {
			return false;
		}
		if ($param[0][1] != 2) {
			return false;
		}
		if ($param[1][0] != 3) {
			return false;
		}
		if ($param[1][1] != 4) {
			return false;
		}
		return true;
	}
	
	/** @return boolean */
	public function stringArrayCall($param) {
		if (!is_array($param)) {
			return false;
		}
		if ($param[0] != 'hello') {
			return false;
		}
		if ($param[1] != 'salut') {
			return false;
		}
		if ($param[2] != 'gutentag') {
			return false;
		}
		return true;
	}
	
	//
	
	/** @return boolean */
	public function boolTrueObjectCall($param) {
		return ($param == true);
	}
	
	/** @return boolean */
	public function boolFalseObjectCall($param) {
		return ($param == false);
	}
	
	/** @return boolean */
	public function byteObjectCall($param) {
		return ($param == 120);
	}
	
	/** @return boolean */
	public function charObjectCall($param) {
		return ($param == 'c');
	}
	
	/** @return boolean */
	public function doubleObjectCall($param) {
		return ($param == 5.5);
	}
	
	/** @return boolean */
	public function floatObjectCall($param) {
		return ($param == 6.6);
	}
	
	/** @return boolean */
	public function intObjectCall($param) {
		return ($param == 1000000000);
	}
	
	/** @return boolean */
	public function longObjectCall($param) {
		return ($param == 5000000000);
	}
	
	/** @return boolean */
	public function shortObjectCall($param) {
		return ($param == 32000);
	}
	
	/** @return boolean */
	public function intObjectArrayCall($param) {
		if (!is_array($param)) {
			return false;
		}
		if ($param[0][0] != 1) {
			return false;
		}
		if ($param[0][1] != 2) {
			return false;
		}
		if ($param[1][0] != 3) {
			return false;
		}
		if ($param[1][1] != 4) {
			return false;
		}
		return true;
	}
	
}