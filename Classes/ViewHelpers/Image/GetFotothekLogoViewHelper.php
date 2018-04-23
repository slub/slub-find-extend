<?php
namespace Slub\SlubFindExtend\ViewHelpers\Image;

  /***************************************************************
   *
   *  Copyright notice
   *
   *  This script is part of the TYPO3 project. The TYPO3 project is
   *  free software; you can redistribute it and/or modify
   *  it under the terms of the GNU General Public License as published by
   *  the Free Software Foundation; either version 3 of the License, or
   *  (at your option) any later version.
   *
   *  The GNU General Public License can be found at
   *  http://www.gnu.org/copyleft/gpl.html.
   *
   *  This script is distributed in the hope that it will be useful,
   *  but WITHOUT ANY WARRANTY; without even the implied warranty of
   *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   *  GNU General Public License for more details.
   *
   *  This copyright notice MUST APPEAR in all copies of the script!
   ***************************************************************/

class GetFotothekLogoViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    protected function getLogoConfig() {
        $logos = [
            [
                'regex' => 'df_*',
                'url' => 'http://www.deutschefotothek.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-df.jpg'
            ],
            [
                'regex' => 'fs_skb_*',
                'url' => 'http://www.lbk-sachsen.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-lbks.jpg'
            ],
            [
                'regex' => 'adf_er_*|adf_ih_*|adf_si_*|adf_hl_*',
                'url' => 'http://www.muenchner-stadtmuseum.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-mue-sm.jpg'
            ],
            [
                'regex' => 'al_pue_*',
                'url' => 'http://sammlung-puescher.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/home_puescher.jpg'
            ],
            [
                'regex' => 'bz_si*|bz_hei*',
                'url' => 'http://www.serbski-institut.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_bz_si.jpg'
            ],
            [
                'regex' => 'sddm*',
                'url' => 'http://www.deutschesdesignmuseum.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-sddm.jpg'
            ],
            [
                'regex' => 'adf_ek_*',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-krull.jpg'
            ],
            [
                'regex' => 'rom_bh_*',
                'url' => 'http://www.biblhertz.it/fotothek/',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_bhrom.jpg'
            ],
            [
                'regex' => 'os_ub_*',
                'url' => 'http://www.bildpostkarten.uni-osnabrueck.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_os_ub.jpg'
            ],
            [
                'regex' => 'z_eth_*',
                'url' => 'http://ba.e-pics.ethz.ch',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-ethbib.jpg'
            ],
            [
                'regex' => 'l_ubl_*',
                'url' => 'http://www.ub.uni-leipzig.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-ubl.jpg'
            ],
            [
                'regex' => 'hh_fcg_*',
                'url' => 'http://www.stiftungfcgundlach.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_fcg.jpg'
            ],
            [
                'regex' => 'abg_rsa_*',
                'url' => 'http://www.cms.residenzschloss-altenburg.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-altenburg.jpg'
            ],
            [
                'regex' => 'k_rjm_*',
                'url' => 'http://www.museenkoeln.de/rautenstrauch-joest-museum/',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-k-rjm.jpg'
            ],
            [
                'regex' => 'adf_cva_*',
                'url' => 'http://www.alvensleben-photography.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_alvensleben.jpg'
            ],
            [
                'regex' => 'adf_gz_*',
                'url' => 'http://www.panfoto.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-panfoto.jpg'
            ],
            [
                'regex' => 'adf_una_*',
                'url' => 'http://www.united-archives.com/',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-ua.jpg'
            ],
            [
                'regex' => 'adf_rw_*',
                'url' => 'http://www.reinhartwolf.de/stiftung.htm',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_wolf-stiftung.jpg'
            ],
            [
                'regex' => 'wue_em_*',
                'url' => 'http://www.archivmehrl.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-mehrl.jpg'
            ],
            [
                'regex' => 'adf_aes_*',
                'url' => 'http://www.alfred-ehrhardt-stiftung.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-eas.jpg',
                'additional' => [
                    [
                        'url' => 'http://www.bpk-bildagentur.de',
                        'logo' => 'http://www.deutschefotothek.de/cms/images/logo-bpk.jpg'
                    ]
                ]
            ],
           [
                'regex' => 'aes_*',
                'url' => 'http://www.alfred-ehrhardt-stiftung.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-eas.jpg'
            ],
            [
                'regex' => 'b_bpk_*',
                'url' => 'http://www.bpk-bildagentur.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-bpk.jpg'
            ],
            [
                'regex' => 'd_cs_*',
                'url' => 'http://www.foticon.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_d_cs.jpg'
            ],
            [
                'regex' => 'adf_lm_*',
                'url' => '',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_sfischer.jpg'
            ],
            [
                'regex' => 'adf_ts_*',
                'url' => '',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_fcg.jpg'
            ],
            [
                'regex' => 'adf_pk_*',
                'url' => '',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_fcg.jpg'
            ],
            [
                'regex' => 'adf_wb_*',
                'url' => '',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_focus160.jpg'
            ],
            [
                'regex' => 'adf_ms_*',
                'url' => '',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_focus160.jpg'
            ],
            [
                'regex' => 'adf_mr_*',
                'url' => 'http://agentur-focus.de/',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_focus160.jpg'
            ],
            [
                'regex' => 'adf_ivk_*',
                'url' => 'http://agentur-focus.de/',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_focus160.jpg'
            ],
            [
                'regex' => 'adf_hh_*',
                'url' => 'http://agentur-focus.de/',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_focus160.jpg'
            ],
            [
                'regex' => 'adf_rm_*',
                'url' => 'http://www.mathiasbertram.de/roger-melis.html',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-melis.jpg'
            ],
            [
                'regex' => 'm_zi_*',
                'url' => 'http://www.zikg.eu/photothek',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_zi.jpg'
            ],
            [
                'regex' => 'hh_ps_*',
                'url' => 'http://galeriehilanehvonkories.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_kories160.jpg'
            ],
            [
                'regex' => 'wob_artur_*',
                'url' => 'http://www.heidersberger.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_heidersberger.jpg'
            ],
            [
                'regex' => 'wob_hei_*',
                'url' => 'http://www.heidersberger.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_heidersberger.jpg'
            ],
/*
            [
                'regex' => 'a99d3:*ismut*',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_wismut.jpg'
            ],
            [
                'regex' => 'a99d3:*DDRBildarchiv*',
                'url' => 'http://www.ddrbildarchiv.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_ddrbildarchiv.jpg'
            ],
            [
                'regex' => 'a99d3:*uroluftbild*',
                'url' => 'http://www.euroluftbild.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_euroluftbild.jpg'
            ],
*/
            [
//                'regex' => 'a99d3:*digiplan*',
                'regex' => 'b_am_*',
                'url' => 'http://architekturmuseum.ub.tu-berlin.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_digiplan.jpg'
            ],
            [
                'regex' => 'rg_*',
                'url' => 'http://www.karl-hans-janke.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_rosengarten.jpg'
            ],
            [
                'regex' => 'm_bsb_*',
                'url' => 'http://www.bsb-muenchen.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_bsb.jpg'
            ],
            [
                'regex' => 'm_digitam_*',
                'url' => 'http://www.architekturmuseum.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_digitam.jpg'
            ],
            [
                'regex' => 'dd_hstad*',
                'url' => 'http://www.staatsarchiv.sachsen.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_dd_hstad.jpg'
            ],
            [
                'regex' => 'col_adhr_*',
                'url' => 'http://www.archives.cg68.fr/',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_col_adhr.jpg'
            ],
            [
                'regex' => 'da_hsta_*',
                'url' => 'http://www.staatsarchiv-darmstadt.hessen.de/',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_da_hsta.jpg'
            ],
            [
                'regex' => 'dd_skd_*|le_ses*|dd_ses*',
                'url' => 'http://www.skd.museum',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_dd_skd.jpg'
            ],
            [
                'regex' => 'fg_dk_*|fg_tu*',
                'url' => 'http://tu-freiberg.de/ze/archiv/',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_fg.jpg'
            ],
            [
                'regex' => 'fg_sta_*',
                'url' => 'https://www.landesarchiv-bw.de/web/47231',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_wer_sta160.jpg'
            ],
            [
                'regex' => 'olb_*',
                'url' => 'http://www.olb.goerlitz.de/',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_gr_olb.jpg'
            ],
            [
                'regex' => 'hal_sta_*',
                'url' => 'http://www.halle.de/de/Kultur-Tourismus/Stadtgeschichte/Stadtarchiv/',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_hal_sta.jpg'
            ],
            [
                'regex' => 'hal_ulb*',
                'url' => 'http://bibliothek.uni-halle.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_hal_ub.jpg'
            ],
            [
                'regex' => 'he_ub_*',
                'url' => 'http://www.ub.uni-heidelberg.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_he_ub.jpg'
            ],
            [
                'regex' => 'k_sta*',
                'url' => 'http://www.rheinisches-bildarchiv.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_koeln.jpg'
            ],
            [
                'regex' => 'k_rba*',
                'url' => 'http://www.rheinisches-bildarchiv.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_koeln.jpg'
            ],
            [
                'regex' => 'ks_ub-lmb*',
                'url' => 'http://www.ub.uni-kassel.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_u-lmb.jpg'
            ],
            [
                'regex' => 'le_sm_*',
                'url' => 'http://www.stadtgeschichtliches-museum-leipzig.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_le_sm.jpg'
            ],
            [
                'regex' => 'mr_hsta_*',
                'url' => 'http://www.staatsarchiv-marburg.hessen.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_mr_hsta.jpg'
            ],
            [
                'regex' => 'n_adh_*',
                'url' => 'http://www.museen.nuernberg.de/duererhaus/',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_n_adh.jpg'
            ],
            [
                'regex' => 'n_gnm_*',
                'url' => 'http://www.gnm.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_n_gnm.jpg'
            ],
            [
                'regex' => 's_hsta_*',
                'url' => 'http://www.landesarchiv-bw.de/web/47272',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_s_hsta.jpg'
            ],
            [
                'regex' => 'sn_lha_*',
                'url' => 'http://www.kulturwerte-mv.de/cms2/LAKD_prod/LAKD/content/de/Landesarchiv/Landeshauptarchiv_Schwerin/index.jsp',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_sn_lha.jpg'
            ],
            [
                'regex' => 'sn_lbmv*',
                'url' => 'http://www.lbmv.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo_lbmv2.jpg'
            ],
            [
                'regex' => 'tu_*|df_tu*',
                'url' => 'http://tu-dresden.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_tud.jpg'
            ],
            [
                'regex' => 'ul_sta*',
                'url' => 'http://www.ulm.de/kultur_tourismus/stadtgeschichte/das_stadtarchiv.3503.3076,3963,4236,3577,3503.htm',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_ul_sta.jpg'
            ],
            [
                'regex' => 'we_ab_*',
                'url' => 'http://www.klassik-stiftung.de/index.php?id=37',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_we_ab.jpg'
            ],
            [
                'regex' => 'wf_hab_*',
                'url' => 'http://www.hab.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_wf_hab.jpg'
            ],
            [
                'regex' => 'bs_haum*',
                'url' => 'http://www.museum-braunschweig.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_bs_haum2.jpg'
            ],
            [
                'regex' => 'wi_hhsta*',
                'url' => 'http://www.hauptstaatsarchiv.hessen.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_wi_hhsta.jpg'
            ],
            [
                'regex' => 'zw_rsb_*',
                'url' => 'http://www.rsb-zwickau.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_zw_rsb.jpg'
            ],
            [
                'regex' => 'dd_stad_*',
                'url' => 'http://www.dresden.de/de/rathaus/aemter-und-einrichtungen/unternehmen/stadtarchiv.php',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_dd_stad.jpg'
            ],
            [
                'regex' => 'dd_lfds_*',
                'url' => 'http://www.lfd.sachsen.de/326.htm',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-dd_lfds.jpg'
            ],
            [
                'regex' => 'dd_kunstbg_*',
                'url' => 'http://www.arthistoricum.net',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_vifaart.jpg'
            ],
            [
                'regex' => 'dd_agk_*',
                'url' => 'http://www.dresden.de/de/rathaus/aemter-und-einrichtungen/oe/dborg/stadt_dresden_6671.php',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-stadt-dd.jpg'
            ],
            [
                'regex' => 'ber_la_*',
                'url' => 'http://www.landesarchiv-berlin.de',
                'logo' => 'http://fotothek.slub-dresden.de/fotos/img/logo_ber_la.jpg'
            ]
/*
            [
                'regex' => '*p_baddr_*',
                'url' => 'http://www.bildatlas-ddr-kunst.de',
                'logo' => 'http://www.deutschefotothek.de/cms/images/logo-bildatlas.jpg'
            ]
*/
    ];
        return $logos;
    }


  /**
  * Get Logo from fotothek config
  *
  * @param string $fields
  * @return string
  */

    public function render($fields) {
        $content = ""; // place holder for error massage

        if ( $this->arguments['fields']['source_id'] == '67' || $this->arguments['fields']['source_id'] == '67-ahn' || $this->arguments['fields']['source_id'] == '67-slub' ) {
            // deutschefotothek
            // record_id => 'oai::a8450::obj|80111251|df_hauptkatalog_0739734'
            $recordId = explode('|', $this->arguments['fields']['record_id']);
            if (isset($recordId[2]) ) {
                $content = $this->renderLogos($logos, $recordId[2]);
            }
        }
        return $content;
    }


    protected function renderLogos($logos, $recordId) {

        $logos = $this->getLogoConfig();
        $content = $this->getLogoHtml($logos[0]); // default Logo is Deutsche Fotothek

        foreach ( $logos as $logo ) {
            $regex = '@^'.$logo['regex'].'@'; // signatur must start with this, see getLogoConfig

            if ( preg_match( $regex, $recordId) ) {
                //  regex found render the logo
                $content = $this->getLogoHtml($logo);

                if ( isset($logo['additional']) ) {
                    // render additional logos
                    foreach ( $logo['additional'] as $aLogo ) {
                        $content .= '<br>'.$this->getLogoHtml($aLogo);
                    }

                }
            }
        }
        if ($content) return $content;
    }


    protected function getLogoHtml($logo) {
        $content = '<img class="detail-logo-img" src="'.$logo['logo'].'">';
        if ( $logo['url'] ) {
            $content  = '<a class="detail-logo-link" target="_blank" href="'.$logo['url'].'">'.$content.'</a>';
        }
        return $content;
    }
}

?>