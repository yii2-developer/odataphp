<?php
/**
  *
  * Copyright (c) 2009, Persistent Systems Limited
  *
  * Redistribution and use, with or without modification, are permitted
  *  provided that the following  conditions are met:
  *   - Redistributions of source code must retain the above copyright notice,
  *     this list of conditions and the following disclaimer.
  *   - Neither the name of Persistent Systems Limited nor the names of its contributors
  *     may be used to endorse or promote products derived from this software
  *     without specific prior written permission.
  *
  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
  * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
  * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
  * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
  * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
  * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
  * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
  * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
  * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
  * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
  */
require_once 'ACSNorthwindEntities.php';
require_once 'urldef.php';
require_once 'settings.php';

echo "<h3>Sample11: This application retrieve and display information of a customer with ID ALFKI from a service which requires ACS authentication. This application
register a callback function using 'OnBeforeRequest'  API, so that before sending any request to the service this call-back will be invoked. From this call-back, application will
connect to ACS to retrieve the acs token, uses this token connects to the service.</h3>";
/**
 * CallBack fnction, this will be invoked before making request to
 * OData service.
 *
 * @param <type> $httpRequest
 * @throws ACSUtilException
 */
function OnBeforeCallBack($httpRequest)
{
    $proxy = new HttpProxy(PROXY_HOST, PROXY_PORT);
    $acsutil = new ACSUtil(SERVICE_NAMESPACE,
                               ACS_USER,
                               ACS_PWD,
                               ACS_APPLIESTO,
                               array(),
                               $proxy);
    try
    {
        echo 'In callback function' . "<br/>";
        //Get the ACS Token
        $token = $acsutil->GetACSToken();
        echo 'Got ACS token:' . "<br/>";
        echo $token . "<br/>";
        //Format the token in the way ODataService Expects
        $authHeaderValue = 'WRAP access_token="' . urldecode($token) . '"';
        //Add the acs auth header
        $httpRequest->Headers->Add('authorization', $authHeaderValue);
    }
    catch(ACSUtilException $ex)
    {
        echo 'Failed to get the ACS token' . "<br/>";
        echo $ex->getError();
        exit;
    }
}

try
{
     $svc = new NorthwindEntities1(ACS_NORTHWIND_SERVICE_URL);
     //Regitser the call-back
     $svc->OnBeforeRequest('OnBeforeCallBack', null);
     
     $query = $svc->Customers()->filter("CustomerID eq 'ALFKI'");
     $customersResponse = $query->Execute();

     if(count($customersResponse->Result))
     {
        echo "<br/><br/>Customer Information <br/><br/>";
        $customer = $customersResponse->Result[0];
        echo 'CustomerID: ' . $customer->CustomerID . "<br/>";
        echo 'CompanyName: ' . $customer->CompanyName . "<br/>";
        echo 'ContactName: ' . $customer->ContactName . "<br/>";
        echo 'ContactTitle: ' . $customer->ContactTitle . "<br/>";
     }
     else
     {
         echo 'Failed to retrieve the Customer with ID \'ALFKI\'';
     }

}
catch(DataServiceRequestException $ex)
{
        echo 'Error: while running the query ' . $ex->Response->getQuery();
        echo "<br/>";
        echo $ex->Response->getError();
}
catch (ODataServiceException $e)
{
    echo "Error:" . $e->getError() . "<br>" . "Detailed Error:" . $e->getDetailedError();
}
?>
