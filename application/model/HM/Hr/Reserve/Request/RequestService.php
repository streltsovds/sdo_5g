<?php
class HM_Hr_Reserve_Request_RequestService extends HM_Service_Abstract
{
    public function setStatus($reserveRequestId, $status)
    {
        if ($reserveRequest = $this->findOne($reserveRequestId)) {
            $reserveRequest->status = $status;
            $this->update($reserveRequest->getValues());

            return $reserveRequest;
        }
        return false;
    }
}
