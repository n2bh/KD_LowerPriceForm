<?php
class Kendesign_Lowerpriceform_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        //Get current layout state
        $this->loadLayout();
        $this->renderLayout();

        echo("I am on Index Controller");
    }

    public function sendfeedbackAction()
    {
        //Get the params in the request
        $params = $this->getRequest()->getParams();
        
        //Get the Email Template Model
        $emailTemplate = Mage::getModel('core/email_template'); 
        $emailTemplate->loadDefault('lowerprice_email_tpl');
        $emailTemplate->setTemplateSubject('Nuova mail di Lower Price');
 
        //Prepare an array with the data from the form
        //TODO: check if the fields need to be sanitize
        $LowerPriceData = array();
        $LowerPriceData['lowerPriceUrl'] = $params['lowerPriceUrl'];
        $LowerPriceData['lowerPriceMyPrice'] = $params['lowerPriceMyPrice'];
        $LowerPriceData['lowerPricePrice'] = $params['lowerPricePrice'];
        $LowerPriceData['lowerPriceShipping'] = $params['lowerPriceShipping'];
        $LowerPriceData['lowerPriceDate'] = $params['lowerPriceDate'];
        $LowerPriceData['lowerPriceSku'] = $params['lowerPriceSku'];
        

        //Save into DB
        $Lowerpriceformmodel = Mage::getModel('lowerpriceform/lowerpriceform');
        $Lowerpriceformmodel->setLowerPriceUrl($LowerPriceData['lowerPriceUrl']);
        $Lowerpriceformmodel->setLowerPriceMyPrice($LowerPriceData['lowerPriceMyPrice']);
        $Lowerpriceformmodel->setLowerPricePrice($LowerPriceData['lowerPricePrice']);
        $Lowerpriceformmodel->setLowerPriceShipping($LowerPriceData['lowerPriceShipping']);

        //I assume that Date Format is %d/%m/%Y
        
        $dateformat = Mage::getStoreConfig('lowerpriceform_options/lowerpriceform_configuration/calendar_dateformat'); 
        $formdate = DateTime::createFromFormat($dateformat, $LowerPriceData['lowerPriceDate'])->format("Y-m-d");

        
        $Lowerpriceformmodel->setLowerPriceDate($formdate);
        $Lowerpriceformmodel->setLowerPriceSku($LowerPriceData['lowerPriceSku']);
        //var_dump($Lowerpriceformmodel);;
        $Lowerpriceformmodel->save();
        
        // Get General email address as the Sender (Admin->Configuration->General->Store Email Addresses)
        $LowerPriceData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
        $LowerPriceData['name'] = Mage::getStoreConfig('trans_email/ident_general/name');
        $emailTemplate->setSenderName($LowerPriceData['name']);
        $emailTemplate->setSenderEmail($LowerPriceData['email']);

        // Get recipient email from the System Configuration
        $configuredEmail = Mage::getStoreConfig('lowerpriceform_options/lowerpriceform_configuration/administrator_email');
        $recipient = Mage::getStoreConfig('trans_email/'. $configuredEmail);
        


        //Assign the POST variables to the template variables
        $emailTemplateVariables['lowerPriceUrl'] = $LowerPriceData['lowerPriceUrl'];
        $emailTemplateVariables['lowerPriceMyPrice'] = $LowerPriceData['lowerPriceMyPrice'];
        $emailTemplateVariables['lowerPricePrice'] = $LowerPriceData['lowerPricePrice'];
        $emailTemplateVariables['lowerPriceShipping'] = $LowerPriceData['lowerPriceShipping'];
        $emailTemplateVariables['lowerPriceDate'] = $LowerPriceData['lowerPriceDate'];
        $emailTemplateVariables['lowerPriceSku'] = $LowerPriceData['lowerPriceSku'];

        $response = array();
        //Send the email
        //TODO: Possibility to select the recipient from the list of the store email addresses
        if($emailTemplate->send($recipient['email'], $recipient['name'], $emailTemplateVariables))
        {
            $response['code'] = 0;
            $response['message'] = 'Everything ok';
        }
        else
        {
            $response['code'] = 0;
            $response['message'] = 'Error sending the email';   
        }

        $response['date'] = $formdate;

        // Return the response in JSON format
        echo(json_encode($response));
    }
}