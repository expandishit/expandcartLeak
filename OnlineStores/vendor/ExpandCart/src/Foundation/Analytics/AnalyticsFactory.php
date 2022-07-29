<?php

namespace ExpandCart\Foundation\Analytics;

use ExpandCart\Foundation\Analytics\SitesManager;
use ExpandCart\Foundation\Analytics\UsersManager;

class AnalyticsFactory
{

    private $trackingCode = <<<TRACK
<script type="text/javascript">
        var _paq = _paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
        _paq.push(["setDomains", ["*."]]);
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u="#siteUrl#";
            _paq.push(['setTrackerUrl', u+'piwik.php']);
            _paq.push(['setSiteId', '#siteId#']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
        })();
</script>
TRACK;

    public function setSetting($db, $token, $siteId, $siteUrl)
    {
        $query = $fields = [];

        $value = [
            'installed' => '1',
            'status' => '1',
            'token_auth' => $token,
            'site_id' => $siteId,
            'tracking_code' => str_replace(
                ['#siteUrl#', '#siteId#'],
                [$siteUrl, $siteId],
                $this->trackingCode
            )
        ];

        $query[] = "INSERT INTO " . DB_PREFIX . "setting SET";
        $fields[] = "store_id = '" . (int)$store_id . "'";
        $fields[] = "`group` = 'analytics'";
        $fields[] = "`key` = 'matomo_analytics'";
        $fields[] = "`value` = '" . $db->escape(serialize($value)) . "'";
        $fields[] = "serialized = '1'";

        $query[] = implode(',', $fields);
        $db->query(implode(' ', $query));
    }

    public function generatePassowrd($storeCode)
    {
        return substr(STORECODE, 0, 2) . '@' . STORECODE . '|' . strrev(STORECODE);
    }

    public function newStore($storeCode, $email, $password, $token)
    {
        $sitesManager = (new SitesManager())->setTokenAuth($token)->setSiteName($storeCode)->addSite();

        $newSite = json_decode($sitesManager->getBody(), true);

        $siteId = $newSite['value'];

        $usersManager = (new UsersManager())->setTokenAuth($token)->setUserLogin($storeCode)
            ->setEmail($email)
            ->setPassword($password)
            ->addUser();

        $user = json_decode($usersManager->getBody(), true);

        $usersManager = (new UsersManager())
            ->setTokenAuth($token)
            ->setUserLogin($storeCode)
            ->setMd5Password(md5($password))
            ->getTokenAuth();

        $tokenAuth = json_decode($usersManager->getBody(), true);

        $usersManager = (new UsersManager())->setTokenAuth($token)->setUserLogin($storeCode)
            ->setAccess('view')
            ->setIdSites($siteId)
            ->setUserAccess();

        $userAccess = json_decode($usersManager->getBody(), true);

        return [
            'siteId' => $siteId,
            'tokenAuth' => $tokenAuth['value']
        ];
    }

    public function getSiteUrl()
    {
        return (new SitesManager())->getSiteUrl();
    }
}
