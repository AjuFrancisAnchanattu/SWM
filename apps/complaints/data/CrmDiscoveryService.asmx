<?xml version="1.0" encoding="utf-8"?>
<wsdl:definitions xmlns:s="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/" xmlns:tns="http://schemas.microsoft.com/crm/2007/CrmDiscoveryService" xmlns:s1="http://microsoft.com/wsdl/types/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:tm="http://microsoft.com/wsdl/mime/textMatching/" xmlns:http="http://schemas.xmlsoap.org/wsdl/http/" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" targetNamespace="http://schemas.microsoft.com/crm/2007/CrmDiscoveryService" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
  <wsdl:types>
    <s:schema elementFormDefault="qualified" targetNamespace="http://schemas.microsoft.com/crm/2007/CrmDiscoveryService">
      <s:import namespace="http://microsoft.com/wsdl/types/" />
      <s:element name="Execute">
        <s:complexType>
          <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="Request" type="tns:Request" />
          </s:sequence>
        </s:complexType>
      </s:element>
      <s:complexType name="Request" abstract="true" />
      <s:complexType name="RetrieveClientPatchesRequest">
        <s:complexContent mixed="false">
          <s:extension base="tns:Request">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="ClientInfo" type="tns:ClientInfo" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="ClientInfo">
        <s:sequence>
          <s:element minOccurs="0" maxOccurs="1" name="PatchIds" type="tns:ArrayOfGuid" />
          <s:element minOccurs="1" maxOccurs="1" name="ClientType" type="tns:ClientTypes" />
          <s:element minOccurs="1" maxOccurs="1" name="UserId" type="s1:guid" />
          <s:element minOccurs="1" maxOccurs="1" name="OrganizationId" type="s1:guid" />
          <s:element minOccurs="1" maxOccurs="1" name="LanguageCode" type="s:int" />
          <s:element minOccurs="0" maxOccurs="1" name="OfficeVersion" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="OSVersion" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="CrmVersion" type="s:string" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="ArrayOfGuid">
        <s:sequence>
          <s:element minOccurs="0" maxOccurs="unbounded" name="guid" type="s1:guid" />
        </s:sequence>
      </s:complexType>
      <s:simpleType name="ClientTypes">
        <s:list>
          <s:simpleType>
            <s:restriction base="s:string">
              <s:enumeration value="OutlookLaptop" />
              <s:enumeration value="OutlookDesktop" />
              <s:enumeration value="DataMigration" />
              <s:enumeration value="OutlookConfiguration" />
              <s:enumeration value="DataMigrationConfiguration" />
            </s:restriction>
          </s:simpleType>
        </s:list>
      </s:simpleType>
      <s:complexType name="RetrieveOrganizationsRequest">
        <s:complexContent mixed="false">
          <s:extension base="tns:Request">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="PassportTicket" type="s:string" />
              <s:element minOccurs="0" maxOccurs="1" name="UserId" type="s:string" />
              <s:element minOccurs="0" maxOccurs="1" name="Password" type="s:string" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="RetrieveCrmTicketRequest">
        <s:complexContent mixed="false">
          <s:extension base="tns:Request">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="OrganizationName" type="s:string" />
              <s:element minOccurs="0" maxOccurs="1" name="PassportTicket" type="s:string" />
              <s:element minOccurs="0" maxOccurs="1" name="UserId" type="s:string" />
              <s:element minOccurs="0" maxOccurs="1" name="Password" type="s:string" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="RetrieveOrganizationExtendedDetailsRequest">
        <s:complexContent mixed="false">
          <s:extension base="tns:Request">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="PassportTicket" type="s:string" />
              <s:element minOccurs="0" maxOccurs="1" name="UserId" type="s:string" />
              <s:element minOccurs="0" maxOccurs="1" name="Password" type="s:string" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="RetrievePolicyRequest">
        <s:complexContent mixed="false">
          <s:extension base="tns:Request" />
        </s:complexContent>
      </s:complexType>
      <s:complexType name="IsEndUserNotificationAvailableRequest">
        <s:complexContent mixed="false">
          <s:extension base="tns:Request">
            <s:sequence>
              <s:element minOccurs="1" maxOccurs="1" name="OrganizationId" type="s1:guid" />
              <s:element minOccurs="1" maxOccurs="1" name="Client" type="tns:EndUserNotificationClient" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:simpleType name="EndUserNotificationClient">
        <s:restriction base="s:string">
          <s:enumeration value="None" />
          <s:enumeration value="WebApplication" />
          <s:enumeration value="Portal" />
          <s:enumeration value="Outlook" />
          <s:enumeration value="Email" />
        </s:restriction>
      </s:simpleType>
      <s:element name="ExecuteResponse">
        <s:complexType>
          <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="Response" type="tns:Response" />
          </s:sequence>
        </s:complexType>
      </s:element>
      <s:complexType name="Response" abstract="true" />
      <s:complexType name="RetrieveOrganizationExtendedDetailsResponse">
        <s:complexContent mixed="false">
          <s:extension base="tns:Response">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="OrganizationExtendedDetails" type="tns:ArrayOfOrganizationExtendedDetail" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="ArrayOfOrganizationExtendedDetail">
        <s:sequence>
          <s:element minOccurs="0" maxOccurs="unbounded" name="OrganizationExtendedDetail" nillable="true" type="tns:OrganizationExtendedDetail" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="OrganizationExtendedDetail">
        <s:sequence>
          <s:element minOccurs="1" maxOccurs="1" name="OrganizationId" type="s1:guid" />
          <s:element minOccurs="0" maxOccurs="1" name="OrganizationName" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="FriendlyName" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="Endpoints" type="tns:ArrayOfOrganizationEndpoint" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="ArrayOfOrganizationEndpoint">
        <s:sequence>
          <s:element minOccurs="0" maxOccurs="unbounded" name="OrganizationEndpoint" nillable="true" type="tns:OrganizationEndpoint" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="OrganizationEndpoint">
        <s:sequence>
          <s:element minOccurs="1" maxOccurs="1" name="AuthenticationType" type="s:int" />
          <s:element minOccurs="0" maxOccurs="1" name="CrmMetadataServiceUrl" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="CrmServiceUrl" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="WebApplicationUrl" type="s:string" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="IsEndUserNotificationAvailableResponse">
        <s:complexContent mixed="false">
          <s:extension base="tns:Response">
            <s:sequence>
              <s:element minOccurs="1" maxOccurs="1" name="IsEndUserNotificationAvailable" type="s:boolean" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="RetrieveClientPatchesResponse">
        <s:complexContent mixed="false">
          <s:extension base="tns:Response">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="PatchInfo" type="tns:ArrayOfClientPatchInfo" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="ArrayOfClientPatchInfo">
        <s:sequence>
          <s:element minOccurs="0" maxOccurs="unbounded" name="ClientPatchInfo" nillable="true" type="tns:ClientPatchInfo" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="ClientPatchInfo">
        <s:sequence>
          <s:element minOccurs="1" maxOccurs="1" name="PatchId" type="s1:guid" />
          <s:element minOccurs="0" maxOccurs="1" name="Title" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="Description" type="s:string" />
          <s:element minOccurs="1" maxOccurs="1" name="IsMandatory" type="s:boolean" />
          <s:element minOccurs="1" maxOccurs="1" name="Depth" type="s:int" />
          <s:element minOccurs="0" maxOccurs="1" name="LinkId" type="s:string" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="RetrieveOrganizationsResponse">
        <s:complexContent mixed="false">
          <s:extension base="tns:Response">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="OrganizationDetails" type="tns:ArrayOfOrganizationDetail" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="ArrayOfOrganizationDetail">
        <s:sequence>
          <s:element minOccurs="0" maxOccurs="unbounded" name="OrganizationDetail" nillable="true" type="tns:OrganizationDetail" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="OrganizationDetail">
        <s:sequence>
          <s:element minOccurs="1" maxOccurs="1" name="OrganizationId" type="s1:guid" />
          <s:element minOccurs="0" maxOccurs="1" name="OrganizationName" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="FriendlyName" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="CrmMetadataServiceUrl" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="CrmServiceUrl" type="s:string" />
          <s:element minOccurs="0" maxOccurs="1" name="WebApplicationUrl" type="s:string" />
        </s:sequence>
      </s:complexType>
      <s:complexType name="RetrieveCrmTicketResponse">
        <s:complexContent mixed="false">
          <s:extension base="tns:Response">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="CrmTicket" type="s:string" />
              <s:element minOccurs="0" maxOccurs="1" name="OrganizationDetail" type="tns:OrganizationDetail" />
              <s:element minOccurs="0" maxOccurs="1" name="ExpirationDate" type="s:string" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
      <s:complexType name="RetrievePolicyResponse">
        <s:complexContent mixed="false">
          <s:extension base="tns:Response">
            <s:sequence>
              <s:element minOccurs="0" maxOccurs="1" name="Policy" type="s:string" />
            </s:sequence>
          </s:extension>
        </s:complexContent>
      </s:complexType>
    </s:schema>
    <s:schema elementFormDefault="qualified" targetNamespace="http://microsoft.com/wsdl/types/">
      <s:simpleType name="guid">
        <s:restriction base="s:string">
          <s:pattern value="[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}" />
        </s:restriction>
      </s:simpleType>
    </s:schema>
  </wsdl:types>
  <wsdl:message name="ExecuteSoapIn">
    <wsdl:part name="parameters" element="tns:Execute" />
  </wsdl:message>
  <wsdl:message name="ExecuteSoapOut">
    <wsdl:part name="parameters" element="tns:ExecuteResponse" />
  </wsdl:message>
  <wsdl:portType name="CrmDiscoveryServiceSoap">
    <wsdl:operation name="Execute">
      <wsdl:documentation xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">Executes requests using a message-based approach. The Execute method takes a message request class as a parameter and returns a message response class.</wsdl:documentation>
      <wsdl:input message="tns:ExecuteSoapIn" />
      <wsdl:output message="tns:ExecuteSoapOut" />
    </wsdl:operation>
  </wsdl:portType>
  <wsdl:binding name="CrmDiscoveryServiceSoap" type="tns:CrmDiscoveryServiceSoap">
    <soap:binding transport="http://schemas.xmlsoap.org/soap/http" />
    <wsdl:operation name="Execute">
      <soap:operation soapAction="http://schemas.microsoft.com/crm/2007/CrmDiscoveryService/Execute" style="document" />
      <wsdl:input>
        <soap:body use="literal" />
      </wsdl:input>
      <wsdl:output>
        <soap:body use="literal" />
      </wsdl:output>
    </wsdl:operation>
  </wsdl:binding>
  <wsdl:binding name="CrmDiscoveryServiceSoap12" type="tns:CrmDiscoveryServiceSoap">
    <soap12:binding transport="http://schemas.xmlsoap.org/soap/http" />
    <wsdl:operation name="Execute">
      <soap12:operation soapAction="http://schemas.microsoft.com/crm/2007/CrmDiscoveryService/Execute" style="document" />
      <wsdl:input>
        <soap12:body use="literal" />
      </wsdl:input>
      <wsdl:output>
        <soap12:body use="literal" />
      </wsdl:output>
    </wsdl:operation>
  </wsdl:binding>
  <wsdl:service name="CrmDiscoveryService">
    <wsdl:port name="CrmDiscoveryServiceSoap" binding="tns:CrmDiscoveryServiceSoap">
      <soap:address location="https://scapadev2.hostedcrm4.net/MSCRMServices/2007/SPLA/CrmDiscoveryService.asmx" />
    </wsdl:port>
    <wsdl:port name="CrmDiscoveryServiceSoap12" binding="tns:CrmDiscoveryServiceSoap12">
      <soap12:address location="https://scapadev2.hostedcrm4.net/MSCRMServices/2007/SPLA/CrmDiscoveryService.asmx" />
    </wsdl:port>
  </wsdl:service>
</wsdl:definitions>