<?php

class Webmaster
{
    private $client_email;
    private $private_key;
    private $scopes;
    private $credentials;

    public function __construct($client_email, $private_key) {
        $this->client_email = $client_email;
        $this->private_key = $private_key;
        $this->scopes = array(Google_Service_Webmasters::WEBMASTERS_READONLY);

        $this->credentials = new Google_Auth_AssertionCredentials(
            $this->client_email,
            $this->scopes,
            $this->private_key
        );
    }

    private function auth(){
        $client = new Google_Client();
        $client->setAssertionCredentials($this->credentials);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        return $client;
    }

    public function test(){
        $domain = 'palmcitypools.com';
        $this_domain = $this->wm_find_site($domain);

        if(!$this_domain){
            die('Can\'t find domain: '.$domain);
        }

        $rows = $this->get_query(
            $this_domain,
            array('2015-10-01', '2015-10-30'),
            array('query' => 'page')
        );
        //$this->pp($rows);
        foreach($rows as $r){
            echo $r->keys[0].', '.$r->impressions.', '.$r->clicks.', '.$r->ctr.', '.$r->position.' <br />';

        }
        exit;

    }

    public function get_query($domain, $date = null, $this_filter = array()){
        $client = $this->auth();
        $webmastersService = new Google_Service_Webmasters($client);
        $searchanalytics = $webmastersService->searchanalytics;

        // Build query
        $request = new Google_Service_Webmasters_SearchAnalyticsQueryRequest;

        $request->setStartDate($date[0]);
        $request->setEndDate($date[1]);

        $active_query = (isset($this_filter['query'])) ? $this_filter['query'] : 'query';
        $request->setDimensions(array($active_query));

        $active_search_type = (isset($this_filter['search_type'])) ? $this_filter['search_type'] : 'web';
        $request->setSearchType($active_search_type);

        if(isset($this_filter['mobile'])){
            $filter = new Google_Service_Webmasters_ApiDimensionFilter;
            $filter->setDimension("device");
            $filter->setExpression("MOBILE");
            $filters = new Google_Service_Webmasters_ApiDimensionFilterGroup;
            $filters->setFilters(array($filter));
            $request->setDimensionFilterGroups(array($filters));
        }

        $qsearch = $searchanalytics->query($domain, $request);

        $rows = $qsearch->getRows();

        return $rows;
    }

    public function wm_get_sites(){
        $client = $this->auth();

        $webmastersService = new Google_Service_Webmasters($client);
        /*get sites*/
        $sites_rs = $webmastersService->sites->listSites();
        $sites = $sites_rs->siteEntry;

        return $sites;
    }

    public function wm_find_site($domain){
        $domain = str_replace('http://' , '', str_replace('www.', '', str_replace('/', '', $domain)));

        $this_domain = '';

        $sites = $this->wm_get_sites();

        /*search for exact_domain*/
        foreach($sites as $site){
            if(strpos(strtolower($site->siteUrl), strtolower($domain)) !== false){
                $this_domain = $site->siteUrl;
            }
        }

        if($this_domain == ''){
            return false;
        }
        return $this_domain;
    }

}