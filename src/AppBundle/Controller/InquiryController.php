<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Inquiry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    /**
     * @Route("/inquiry")
	*/
class InquiryController extends Controller
{
    /**
     * @Route("/")
     * @Method("get")
     */
    public function indexAction()
    {
        return $this->render("Inquiry/index.html.twig", ['form'=>$this->createInquiryView()->createView()]);
    }

    /**
     * @Route("/complete")
     */
    public function completeAction()
    {
        return $this->render("Complete/index.html.twig");
    }

    /**
     * @Route("/")
     * @Method("post")
     */
    public function indexPostAction(Request $request)
    {
    	$form = $this->createInquiryView();
    	$form->handleRequest($request);
    	if($form->isValid()){

            $inquiry = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($inquiry);
            $em->flush();

            $message = \Swift_Message::newInstance()
                        ->setSubject('Webサイトからのお問い合わせ')
                        ->setFrom('hanai.develop@gmail.com')
                        ->setTo('hanai.develop@gmail.com')
                        ->setBody(
                            $this->renderView(
                                'mail/inquiry.txt.twig',
                                ['data' => $inquiry]
                            )
                        );
            $this->get('mailer')->send($message);
            return $this->redirect($this->generateUrl('app_inquiry_complete'));
    	}
        return $this->render("Inquiry/index.html.twig", ['form'=>$form->createView()]);
    }

    private function createInquiryView()
    {
    	$builder = $this->createFormBuilder(new Inquiry());
    	$form = $builder
    	// 組み立て指示
		->add('name','text')
		->add('email','text')
		->add('tel', 'text', ['required' => false])
		->add('type','choice', [
    				'choices' => [
    					'公演について',
    					'その他'
    				],
    				'expanded' => true
    				])
			->add('content','textarea')
			->add('submit','submit',['label' => '送信'])
		// 組み立てた結果をくれ
		->getForm();

		return $form;
    }

}
