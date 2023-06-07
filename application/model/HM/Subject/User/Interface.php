<?php
interface HM_Subject_User_Interface {

	public function isSubjectUnaccessible($assignment, $subject);

	public function getSubjectDates($assignment, $subject);
}
?>