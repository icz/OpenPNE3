<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * monitoringFunction actions.
 *
 * @package    OpenPNE
 * @subpackage admin
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class monitoringFunctionActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('monitoringFunction', 'imageList');
  }

 /**
  * Executes imageList action
  *
  * @param sfRequest $request A request object
  */
  public function executeImageList(sfWebRequest $request)
  {
    $this->pager = Doctrine::getTable('File')
      ->getImageFilePager($request->getParameter('page', 1), $request->getParameter('size', 20));
  }


 /**
  * Executes deleteImage action
  *
  * @param sfRequest $request A request object
  */
  public function executeDeleteImage(sfWebRequest $request)
  {
    $this->image = Doctrine::getTable('File')
      ->find($request->getParameter('id'));

    $this->form = new sfForm();

    if ($request->isMethod(sfWebRequest::POST)) {
      $request->checkCSRFProtection();

      $this->image->delete();
      $this->getUser()->setFlash('notice', '画像の削除が完了しました');

      $this->redirect('monitoringFunction/imageList');
    }
  }

 /**
  * Executes editImage action
  *
  * @param sfRequest $request A request object
  */
  public function executeEditImage(sfWebRequest $request)
  {
    $this->form = new ImageForm();

    if ($request->isMethod(sfWebRequest::POST))
    {
        $this->form->bindAndSave
        (
          $request->getParameter('image'),
          $request->getFiles('image')
        );
        $this->getUser()->setFlash('notice', '画像の追加が完了しました');
        $this->redirect('monitoringFunction/imageList');
    }
  }

 /**
  * Executes fileList action
  *
  * @param sfRequest $request A request object
  */
  public function executeFileList(sfWebRequest $request)
  {
    $this->pager = Doctrine::getTable('File')
      ->getFilePager($request->getParameter('page', 1), $request->getParameter('size', 20));
  }

 /**
  * Executes fileDelete action
  *
  * @param sfRequest $request A request object
  */
  public function executeDeleteFile(sfWebRequest $request)
  {
    $this->file = Doctrine::getTable('File')
      ->find($request->getParameter('id'));

    $this->form = new sfForm();

    if ($request->isMethod(sfWebRequest::POST)) {
      $request->checkCSRFProtection();

      $this->file->delete();
      $this->getUser()->setFlash('notice', 'ファイルの削除が完了しました');
      $this->redirect('monitoringFunction/fileList');
    }
  }

 /**
  * Executes downloadFile action
  *
  * @param sfRequest $request A request object
  */
  public function executeFileDownload(sfWebRequest $request)
  {
    $file = Doctrine::getTable('File')->find($request->getParameter('id'));
    opToolkit::fileDownload(
      $file->getOriginalFilename(),
      $file->getFileBin()->getBin()
    );
  }
}
