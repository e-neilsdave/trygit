<?php
stream_context_set_default( [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);
require_once('dsc/autoload.php');

class DocuSignSample
{
    public function signatureRequestFromTemplate()
    {
     
		$username = "dave.nilesh@rayosun.com";
        $password = "321#Nilesh@";
        $integrator_key = "455e8781-5535-4cce-a435-21544ab9d994";     

        // change to production (www.docusign.net) before going live
        $host = "https://demo.docusign.net/restapi";

        // create configuration object and configure custom auth header
        $config = new DocuSign\eSign\Configuration();
        $config->setHost($host);
        $config->addDefaultHeader("X-DocuSign-Authentication", "{\"Username\":\"" . $username . "\",\"Password\":\"" . $password . "\",\"IntegratorKey\":\"" . $integrator_key . "\"}");

        // instantiate a new docusign api client
        $apiClient = new DocuSign\eSign\ApiClient($config);
        $accountId = null;
        
        try 
        {
            //*** STEP 1 - Login API: get first Account ID and baseURL
            $authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);
            $options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
            $loginInformation = $authenticationApi->login($options);
            if(isset($loginInformation) && count($loginInformation) > 0)
            {
                $loginAccount = $loginInformation->getLoginAccounts()[0];
                $host = $loginAccount->getBaseUrl();
                $host = explode("/v2",$host);
                $host = $host[0];
	
                // UPDATE configuration object
                $config->setHost($host);
		
                // instantiate a NEW docusign api client (that has the correct baseUrl/host)
                $apiClient = new DocuSign\eSign\ApiClient($config);
	
                if(isset($loginInformation))
                {
                    $accountId = $loginAccount->getAccountId();
                    /*if(!empty($accountId))
                    {
                        //*** STEP 2 - Signature Request from a Template
                        // create envelope call is available in the EnvelopesApi
                        $envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);
                        // assign recipient to template role by setting name, email, and role name.  Note that the
                        // template role name must match the placeholder role name saved in your account template.
                        $templateRole = new  DocuSign\eSign\Model\TemplateRole();
                        $templateRole->setEmail("dave.nilesh@rayosun.com");
                        $templateRole->setName("Nilesh Dave");
                        $templateRole->setRoleName("Project Lead");             

                        // instantiate a new envelope object and configure settings
                        $envelop_definition = new DocuSign\eSign\Model\EnvelopeDefinition();
                        $envelop_definition->setEmailSubject("[DocuSign PHP SDK] - Signature Request Sample");
                        $envelop_definition->setTemplateId("[TEMPLATE_ID]");
                        $envelop_definition->setTemplateRoles(array($templateRole));
                        
                        // set envelope status to "sent" to immediately send the signature request
                        $envelop_definition->setStatus("sent");

                        // optional envelope parameters
                        $options = new \DocuSign\eSign\Api\EnvelopesApi\CreateEnvelopeOptions();
                        $options->setCdseMode(null);
                        $options->setMergeRolesOnDraft(null);

                        // create and send the envelope (aka signature request)
                        $envelop_summary = $envelopeApi->createEnvelope($accountId, $envelop_definition, $options);
                        if(!empty($envelop_summary))
                        {
                            echo "$envelop_summary";
                        }
                    }*/
					if(!empty($accountId)){
					
						/////////////////////////////////////////////////////////////////////////
						// STEP 2:  Create & Send Envelope (aka Signature Request)
						/////////////////////////////////////////////////////////////////////////

						// set recipient information
						$recipientName = "Nilesh Dave";
						$recipientEmail = "dave.nilesh@rayosun.com";

						// configure the document we want signed
						$documentFileName = "auth.pdf";
						$documentName = "SignTest1.pdf";
						#echo __DIR__."\\".$documentFileName;exi
						#echo base64_encode(file_get_contents(__DIR__ . "\\".$documentFileName));exit;

						// instantiate a new envelopeApi object
						$envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);

						// Add a document to the envelope
						$document = new DocuSign\eSign\Model\Document();
						$document->setDocumentBase64(base64_encode(file_get_contents(__DIR__ . '\\'.$documentFileName)));
						$document->setName($documentName);
						$document->setDocumentId("13");

						// Create a |SignHere| tab somewhere on the document for the recipient to sign
						$signHere = new \DocuSign\eSign\Model\SignHere();
						$signHere->setXPosition("100");
						$signHere->setYPosition("100");
						$signHere->setDocumentId("1");
						$signHere->setPageNumber("1");
						$signHere->setRecipientId("1");

						// add the signature tab to the envelope's list of tabs
						$tabs = new DocuSign\eSign\Model\Tabs();
						$tabs->setSignHereTabs(array($signHere));

						// add a signer to the envelope
						$signer = new \DocuSign\eSign\Model\Signer();
						$signer->setEmail($recipientEmail);
						$signer->setName($recipientName);
						$signer->setRecipientId("12");
						$signer->setTabs($tabs);

						// Add a recipient to sign the document
						$recipients = new DocuSign\eSign\Model\Recipients();
						$recipients->setSigners(array($signer));
						$envelop_definition = new DocuSign\eSign\Model\EnvelopeDefinition();
						$envelop_definition->setEmailSubject("[DocuSign PHP SDK] - Please sign this doc");

						// set envelope status to "sent" to immediately send the signature request
						$envelop_definition->setStatus("sent");
						$envelop_definition->setRecipients($recipients);
						$envelop_definition->setDocuments(array($document));

						// create and send the envelope! (aka signature request)
						$envelop_summary = $envelopeApi->createEnvelope($accountId, $envelop_definition, null);

						echo "$envelop_summary\n";
					
					
					
					}
                }
            }
        }
        catch (DocuSign\eSign\ApiException $ex)
        {
            echo "Exception: " . $ex->getMessage() . "\n";
        }
    }
}
$obj= new DocuSignSample();
$obj->signatureRequestFromTemplate();

?>
