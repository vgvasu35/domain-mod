<?php
/**
 * /classes/DomainMOD/Porkbun.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2025 Greg Chetcuti <greg@greg.ca>
 *
 * Project: http://domainmod.org   Author: https://greg.ca
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
//@formatter:off
namespace DomainMOD;

class Porkbun
{
    public $format;
    public $log;

    public function __construct()
    {
        $this->format = new Format();
        $this->log = new Log('class.porkbun');
    }

    public function getApiUrl($domain, $command)
    {
        $base_url = 'https://api.porkbun.com/api/json/v3';

        if ($command == 'domainlist') {
            return $base_url . '/domain/listAll';
        } elseif ($command == 'dnsservers') {
            return $base_url . '/domain/getNs/' . $domain;
        } else {
            return _('Unable to build API URL');
        }

    }

    public function apiCall($api_key, $api_secret, $full_url)
    {
        $post_data = json_encode(array(
            'apikey' => $api_key,
            'secretapikey' => $api_secret));

        $handle = curl_init($full_url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($handle);
        curl_close($handle);
        return $result;
    }

    public function getDomainList($api_key, $api_secret)
    {
        $domain_list = array();
        $domain_count = 0;

        $api_url = $this->getApiUrl('', 'domainlist');
        $api_results = $this->apiCall($api_key, $api_secret, $api_url);
        $array_results = $this->convertToArray($api_results);

        // confirm that the api call was successful
        if (isset($array_results['domains'])) {

            foreach ($array_results['domains'] as $domain_details) {

                $domain_list[] = $domain_details['domain'];
                $domain_count++;

            }

        } else {

            $log_message = 'Unable to get domain list';
            $log_extra = array('API Key' => $this->format->obfusc($api_key), 'API Secret' => $this->format->obfusc($api_secret));
            $this->log->error($log_message, $log_extra);

        }

        return array($domain_count, $domain_list);
    }

    public function getFullInfo($api_key, $api_secret, $domain)
    {
        $expiration_date = '';
        $dns_servers = array();
        $privacy_status = '';
        $autorenewal_status = '';

        // Porkbun doesn't currently allow you to retrieve the details for a single domain
        // so we need to get the full list and then match the results against the domain we're looking for
        $api_url = $this->getApiUrl('', 'domainlist');
        $api_results = $this->apiCall($api_key, $api_secret, $api_url);
        $array_results = $this->convertToArray($api_results);

        // confirm that the api call was successful
        if (isset($array_results['domains'])) {

            foreach ($array_results['domains'] as $domain_details) {

                if ($domain == $domain_details['domain']) {

                    // get expiration date
                    $expiration_date = substr($domain_details['expireDate'], 0, 10);

                    // get dns servers
                    $dns_result = $this->getDnsServers($api_key, $api_secret, $domain);
                    $dns_servers = $this->processDns($dns_result);

                    // get privacy status
                    $privacy_result = (string) $domain_details['whoisPrivacy'];
                    $privacy_status = $this->processPrivacy($privacy_result);

                    // get auto renewal status
                    $autorenewal_result = (string) $domain_details['autoRenew'];
                    $autorenewal_status = $this->processAutorenew($autorenewal_result);

                }

            }

        } else {

            $log_message = 'Unable to get domain details';
            $log_extra = array('Domain' => $domain, 'API Key' => $this->format->obfusc($api_key), 'API Secret' => $this->format->obfusc($api_secret));
            $this->log->error($log_message, $log_extra);

        }

        return array($expiration_date, $dns_servers, $privacy_status, $autorenewal_status);
    }

    public function getDnsServers($api_key, $api_secret, $domain)
    {
        $dns_servers = array();

        $api_url = $this->getApiUrl($domain, 'dnsservers');
        $api_results = $this->apiCall($api_key, $api_secret, $api_url);
        $array_results = $this->convertToArray($api_results);

        // confirm that the api call was successful
        if (isset($array_results['ns'])) {

            $dns_servers = $array_results['ns'];

        } else {

            $log_message = 'Unable to get DNS servers';
            $log_extra = array('Domain' => $domain, 'API Key' => $this->format->obfusc($api_key), 'API Secret' => $this->format->obfusc($api_secret));
            $this->log->error($log_message, $log_extra);

        }

        return $dns_servers;
    }

    public function convertToArray($api_result)
    {
        return json_decode($api_result, true);
    }

    public function processDns($dns_result)
    {
        $dns_servers = array();
        if (!empty($dns_result)) {
            $dns_servers = array_filter($dns_result);
        } else {
            $dns_servers[0] = 'no.dns-servers.1';
            $dns_servers[1] = 'no.dns-servers.2';
        }
        return $dns_servers;
    }

    public function processPrivacy($privacy_result)
    {
        if ($privacy_result == '') {
            $privacy_status = '0';
        } else {
            $privacy_status = '1';
        }
        return $privacy_status;
    }

    public function processAutorenew($autorenewal_result)
    {
        if ($autorenewal_result == '') {
            $autorenewal_status = '0';
        } else {
            $autorenewal_status = '1';
        }
        return $autorenewal_status;
    }

} //@formatter:on
