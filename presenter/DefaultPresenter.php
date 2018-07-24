<?php

namespace Absolute\Module\Project\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter; 

class DefaultPresenter extends ProjectBasePresenter {

    /** @var \Absolute\Module\Label\Manager\LabelManager @inject */
    public $labelManager;

    public function startup() {
        parent::startup();
    }

    public function renderLabel($urlId, $urlId2) {
        switch ($this->httpRequest->getMethod()) {
            case 'GET':
                if (!isset($urlId)) 
                    $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
                 else {
                    if (isset($urlId2)) {
                        $this->_getProjectLabelRequest($urlId, $urlId2);
                    } else {
                        $this->_getProjectLabelListRequest($urlId);
                    }
                }
                break;
            case 'POST':
                $this->_postProjectLabelRequest($urlId, $urlId2);
                break;
            case 'DELETE':
                $this->_deleteProjectLabelRequest($urlId, $urlId2);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }
    //Project
    private function _getProjectLabelListRequest($idProject) {
        $projectsList = $this->labelManager->getProjectList($idProject);
        if(!$projectsList){
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        }else{
            $this->jsonResponse->payload = $projectsList;
            $this->httpResponse->setCode(Response::S200_OK);
            
        }
    }

    private function _getProjectLabelRequest($projectId, $labelId) {
        $ret=$this->labelManager->getProjectItem($projectId,$labelId);
        if(!$ret){
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        }else{
            $this->jsonResponse->payload = $ret;
            $this->httpResponse->setCode(Response::S200_OK);
            
        }
    }

    private function _postProjectLabelRequest($urlId, $urlId2) {
        if(!isset($urlId)||!isset($urlId2)){
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->labelManager->labelProjectCreate($urlId, $urlId2);
        if (!$ret) {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        } else {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S201_CREATED);
        }
    }

    private function _deleteProjectLabelRequest($urlId, $urlId2) {
        if(!isset($urlId)||!isset($urlId2)){
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->labelManager->labelProjectDelete($urlId, $urlId2);
        if (!$ret) {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        } else {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

}
