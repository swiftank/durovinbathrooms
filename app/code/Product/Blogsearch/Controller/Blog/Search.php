<?php

namespace Product\Blogsearch\Controller\Blog;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class Search extends Action
{
    /**
     * @var \Magento\Framework\App\Action\Contex
     */
    private $context;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(Context $context, RequestInterface $request) {
        parent::__construct($context);
        $this->context  = $context;
        $this->request = $request;
    }
    
    /**
     * @return json
     */
    public function execute()
    {
        return $this->getResponse();  
    }

    public function getResponse(){
        $query = $this->context->getRequest()->getParams('q');
        $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
        $count = $connection->fetchAll("SELECT COUNT(post_id) as postcount FROM magefan_blog_post where (is_active = 1 AND title LIKE  '%" . $query['q'] . "%')  OR  (is_active = 1 AND content LIKE  '%" . $query['q'] . "%')");
        $rows = $connection->fetchAll("SELECT title, identifier FROM magefan_blog_post where (is_active = 1 AND title LIKE  '%" . $query['q'] . "%')  OR  (is_active = 1 AND content LIKE  '%" . $query['q'] . "%') LIMIT 3");
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["data" => $rows , "count" => $count[0]['postcount']]);
        return $resultJson;
    }

}
