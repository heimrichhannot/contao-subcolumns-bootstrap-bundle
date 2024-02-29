<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\FormField\FormColStart;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class FormFieldContainer
{

    /** Write the other Sets */
    public function onSubmit(DataContainer $dc): void
    {
        if ($dc->activeRecord->type != FormColStart::TYPE
            || $dc->activeRecord->fsc_type == "")
        {
            return;
        }

        $strSet = SubColumnsBootstrapBundle::getProfile();

        $id = $dc->id;

        $sorting = $dc->activeRecord->sorting;

        $arrColset = $GLOBALS['TL_SUBCL'][$strSet]['sets'][$dc->activeRecord->fsc_type];

        $arrChilds = $dc->activeRecord->fsc_childs != "" ? unserialize($dc->activeRecord->fsc_childs) : "";

        if ($dc->activeRecord->fsc_gapuse == 1)
        {
            $gap_value = $dc->activeRecord->fsc_gap != "" ? $dc->activeRecord->fsc_gap : '12';
        }

        $intColcount = count($arrColset) - 2;

        /* Neues Spaltenset anlegen */
        if ($arrChilds == '')
        {
            $arrChilds = array();

            $this->moveRows($dc->activeRecord->pid,$dc->activeRecord->sorting,128 * ( count($arrColset) + 1 ));

            $arrSet = [
                'pid' => $dc->activeRecord->pid,
                'tstamp' => time(),
                'sorting'=>0,
                'type' => 'formcolpart',
                'fsc_name'=> '',
                'fsc_type'=>$dc->activeRecord->fsc_type,
                'fsc_parent'=>$dc->activeRecord->id,
                'fsc_sortid'=>0,
                'fsc_gapuse' => $dc->activeRecord->fsc_gapuse,
                'fsc_gap' => $dc->activeRecord->fsc_gap,
                'fsc_color' => $dc->activeRecord->fsc_color
            ];

            for($i=1;$i<=$intColcount+1;$i++)
            {

                $arrSet['sorting'] = $dc->activeRecord->sorting+($i+1)*64;
                $arrSet['fsc_name'] = $dc->activeRecord->fsc_name.'-Part-'.($i);
                $arrSet['fsc_sortid'] = $i;

                $insertElement = $this->Database->prepare("INSERT INTO tl_form_field %s")
                    ->set($arrSet)
                    ->execute()
                    ->insertId;

                $arrChilds[] = $insertElement;
            }

            $arrSet['sorting'] = $dc->activeRecord->sorting+($i+1)*64;
            $arrSet['type'] = 'formcolend';
            $arrSet['fsc_name'] = $dc->activeRecord->fsc_name.'-End';
            $arrSet['fsc_sortid'] = $intColcount+2;

            $insertElement = $this->Database->prepare("INSERT INTO tl_form_field %s")
                ->set($arrSet)
                ->execute()
                ->insertId;

            $arrChilds[] = $insertElement;

            $insertElement = $this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
                ->set(array('fsc_childs'=>$arrChilds,'fsc_parent'=>$dc->activeRecord->id,))
                ->execute($dc->id);

            return true;

        }

        /* Gleiche Spaltenzahl */
        if(count($arrChilds) == count($arrColset))
        {
            $intLastElement = array_pop($arrChilds);

            $i = 1;
            foreach($arrChilds as $v)
            {
                $arrSet = array('fsc_type' => $dc->activeRecord->fsc_type,
                                'fsc_gapuse' => $dc->activeRecord->fsc_gapuse,
                                'fsc_gap' => $dc->activeRecord->fsc_gap,
                                'fsc_name' => $dc->activeRecord->fsc_name.'-Part-'.($i++),
                                'fsc_color' => $dc->activeRecord->fsc_color
                );

                $this->Database->prepare("UPDATE tl_form_field %s WHERE id=".$v)
                    ->set($arrSet)
                    ->execute();
            }

            $arrSet = array('fsc_type' => $dc->activeRecord->fsc_type,
                            'fsc_gapuse' => $dc->activeRecord->fsc_gapuse,
                            'fsc_gap' => $dc->activeRecord->fsc_gap,
                            'fsc_name' => $dc->activeRecord->fsc_name.'-End',
                            'fsc_color' => $dc->activeRecord->fsc_color
            );

            $this->Database->prepare("UPDATE tl_form_field %s WHERE id=".$intLastElement)
                ->set($arrSet)
                ->execute();



            return true;

        }

        /* Weniger Spalten */
        if(count($arrChilds) > count($arrColset))
        {

            $intDiff = count($arrChilds) - count($arrColset);

            for($i=1;$i<=$intDiff;$i++)
            {
                $intChildId = array_pop($arrChilds);
                $this->Database->prepare("DELETE FROM tl_form_field WHERE id=?")
                    ->execute($intChildId);

            }

            $this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
                ->set(array('fsc_childs'=>$arrChilds))
                ->execute($dc->id);

            /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
            $arrSet = array('fsc_type' => $dc->activeRecord->fsc_type,
                            'fsc_gapuse' => $dc->activeRecord->fsc_gapuse,
                            'fsc_gap' => $dc->activeRecord->fsc_gap,
                            'fsc_color' => $dc->activeRecord->fsc_color
            );

            foreach($arrChilds as $value)
            {

                $this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
                    ->set($arrSet)
                    ->execute($value);

            }

            /*  Den Typ des letzten Elements auf End-ELement umsetzen und FSC-namen anpassen */
            $intChildId = array_pop($arrChilds);

            $arrSet['fsc_name'] = $dc->activeRecord->fsc_name.'-End';
            $arrSet['type'] = 'formcolend';

            $this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
                ->set($arrSet)
                ->execute($intChildId);

            return true;
        }

        /* Mehr Spalten */
        if(count($arrChilds) < count($arrColset))
        {

            $intDiff = count($arrColset) - count($arrChilds);

            $objEnd = $this->Database->prepare("SELECT id,sorting,fsc_sortid FROM tl_form_field WHERE id=?")->execute($arrChilds[count($arrChilds)-1]);

            $this->moveRows($dc->activeRecord->pid,$objEnd->sorting,64 * ( $intDiff) );

            /*  Den Typ des letzten Elements auf End-ELement umsetzen und FSC-namen anpassen */
            $intChildId	= count($arrChilds);
            $arrSet['fsc_name'] = $dc->activeRecord->fsc_name.'-Part-'.($intChildId);
            $arrSet['type'] = 'formcolpart';

            $this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
                ->set($arrSet)
                ->execute($objEnd->id);



            $intFscSortId = $objEnd->fsc_sortid;
            $intSorting = $objEnd->sorting;

            $arrSet = array('type' => 'formcolpart',
                            'pid' => $dc->activeRecord->pid,
                            'tstamp' => time(),
                            'sorting' => 0,
                            'fsc_name' => '',
                            'fsc_type' => $dc->activeRecord->fsc_type,
                            'fsc_parent' => $dc->id,
                            'fsc_sortid' => 0,
                            'fsc_gapuse' => $dc->activeRecord->fsc_gapuse,
                            'fsc_gap' => $dc->activeRecord->fsc_gap,
                            'fsc_color' => $dc->activeRecord->fsc_color
            );

            $intDiff;

            if($intDiff>0)
            {

                /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
                for($i=1;$i<$intDiff;$i++)
                {
                    ++$intChildId;
                    ++$intFscSortId;
                    $intSorting += 64;
                    $arrSet['fsc_name'] = $dc->activeRecord->fsc_name.'-Part-'.($intChildId);
                    $arrSet['fsc_sortid'] = $intFscSortId;
                    $arrSet['sorting'] = $intSorting;

                    $objInsertElement = $this->Database->prepare("INSERT INTO tl_form_field %s")
                        ->set($arrSet)
                        ->execute();

                    $insertElement = $objInsertElement->insertId;

                    $arrChilds[] = $insertElement;

                }


            }

            /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
            $arrData = array('fsc_type' => $dc->activeRecord->fsc_type,
                             'fsc_gapuse' => $dc->activeRecord->fsc_gapuse,
                             'fsc_gap' => $dc->activeRecord->fsc_gap,
                             'fsc_color' => $dc->activeRecord->fsc_color
            );

            foreach($arrChilds as $value)
            {

                $this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
                    ->set($arrData)
                    ->execute($value);

            }

            /* Neues End-element erzeugen */
            $arrSet['sorting'] = $intSorting + 64;
            $arrSet['type'] = 'formcolend';
            $arrSet['fsc_name'] = $dc->activeRecord->fsc_name.'-End';
            $arrSet['fsc_sortid'] = ++$intFscSortId;

            $insertElement = $this->Database->prepare("INSERT INTO tl_form_field %s")
                ->set($arrSet)
                ->execute()
                ->insertId;

            $arrChilds[] = $insertElement;

            /* Kindelemente in Startelement schreiben */
            $insertElement = $this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
                ->set(array('fsc_childs'=>$arrChilds))
                ->execute($dc->id);

            return true;

        }
    }
}