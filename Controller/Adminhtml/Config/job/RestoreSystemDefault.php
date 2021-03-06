<?php

namespace EthanYehuda\CronjobManager\Controller\Adminhtml\Config\Job;

use EthanYehuda\CronjobManager\Helper\JobConfig;
use Magento\Framework\App\CacheInterface;

class RestoreSystemDefault extends \Magento\Backend\App\Action
{
    const SYSTEM_DEFAULT_IDENTIFIER = 'system_default';
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;
    
    /**
     * @var ManagerFactory
     */
    private $managerFactory;
    
    /**
     * @var CacheInterface
     */
    private $cache;
    
    /**
     * @var JobConfig
     */
    private $helper;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\App\Action\Context $context,
        CacheInterface $cache,
        JobConfig $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->cache = $cache;
        $this->helper = $helper;
    }

    /**
     * {@inheritDoc}
     * @see \Magento\Backend\App\AbstractAction::_isAllowed()
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('EthanYehuda_CronjobManager::cronjobmanager');
    }

    /**
     * Save cronjob
     *
     * @return Void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $jobCode = $params['job_code'] ? $params['job_code'] : null;
        if (!$jobCode) {
            $this->getMessageManager()->addErrorMessage("Something went wrong when recieving the request");
            $this->_redirect('*/config/edit/');
            return;
        }
        $group = $params['group'] ? $params['group'] : null;
        try {
            $path = $this->helper->constructFrequencyPath($jobCode, $group);
            $this->helper->restoreSystemDefault($path);
            $this->cache->remove(self::SYSTEM_DEFAULT_IDENTIFIER);
        } catch (\Exception $e) {
            $this->getMessageManager()->addErrorMessage($e->getMessage());
            unset($params['key'], $params['form_key']);
            $this->_redirect('*/config/edit/', $params);
            return;
        }
        $this->getMessageManager()->addSuccessMessage("Successfully restored Cron Job: {$jobCode} to system defaults");
        $this->_redirect('*/config/index/');
    }
}
