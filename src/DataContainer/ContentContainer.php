<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ContentContainer
{
    public function __construct(
        private ContaoCsrfTokenManager $tokenManager,
        private Connection $connection
    ) {}

    /**
     * Autogenerate a name for the colset if it has not yet been set
     */
    public function scName_onSaveCallback(mixed $varValue, DataContainer $dc): string
    {
        // Generate alias if there is none
        return strlen($varValue) ? $varValue : ('colset.' . $dc->id);
    }

    /**
     * Get the col-sets depending on the selection from the settings
     */
    public function getAllTypes(): array
    {
        $strSet = SubColumnsBootstrapBundle::getProfile();
        return array_keys($GLOBALS['TL_SUBCL'][$strSet]['sets'] ?? []);
    }

    /**
     * get all column-sets
     */
    public function getColumnsetOptions(DataContainer $dc): array
    {
        $collection = ColumnsetModel::findBy(
            'published=1 AND columns',
            $dc->activeRecord->sc_type,
            ['order' => 'title']
        );

        $set = [];

        if ($collection !== null) {
            while ($collection->next()) {
                $set[$collection->id] = $collection->title;
            }
        }

        return $set;
    }

    public function columnsetIdWizard(DataContainer $dc): string
    {
        $id = (int)$dc->value;

        if ($id < 1) {
            return '';
        }

        $module = 'columnset';

        $url = Environment::get('url')
            . parse_url(Environment::get('uri'), PHP_URL_PATH);

        if (!$id) {
            return '';
        }

        $label = sprintf(StringUtil::specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1] ?? 'wizard'), $id);

        return sprintf(
            ' <a href="'.$url.'?do=%s&amp;act=edit&amp;id=%s&amp;popup=1&amp;nb=1&amp;rt=%s" title="%s" '
            . 'style="padding-left: 5px; padding-top: 2px; display: inline-block;" onclick="%s;return false">%s</a>',
            $module,
            $id,
            $this->tokenManager->getDefaultTokenValue(),
            $label,
            1024,
            $label,
            "Backend.openModalIframe({'width':%s,'title':'%s','url':this.href})",
            Image::getHtml('alias.svg', $label, 'style="vertical-align:top"')
        );
    }

    public function toggleAdditionalElements($varValue, DataContainer $dc)
    {
        if ($dc->id)
        {
            $stmt = $this->connection
                ->prepare("UPDATE tl_content SET tstamp=:tstamp, invisible=:inv WHERE sc_parent=? AND type!=?");
            $stmt->bindValue('tstamp', time());
            $stmt->bindValue('inv', $varValue ? 1 : '');
            $stmt->executeStatement([$dc->id, 'colsetStart']);
        }
        return $varValue;
    }

    public function createPalette(DataContainer $dc): void
    {
        PaletteManipulator::create()
            ->removeField('sc_type', 'colset_legend')
            ->removeField('columnset_id', 'colset_legend')
            ->applyToPalette('colsetStart', 'tl_content');

        if (!class_exists('onemarshall\AosBundle\AosBundle')) {
            return;
        }

        $palette = $GLOBALS['TL_DCA']['tl_content']['palettes']['colsetStart'];

        $palette = str_replace('invisible,', '', $palette) . ';{invisible_legend:hide},invisible;{aos_legend:hide},
                aosAnimation,
                aosEasing,
                aosDuration,
                aosDelay,
                aosAnchor,
                aosAnchorPlacement,
                aosOffset,
                aosOnce;
                {invisible_legend:hide}';

        $GLOBALS['TL_DCA']['tl_content']['palettes']['colsetStart'] = $palette;
    }

    /**
     * Write the other Sets
     * @throws Exception
     * */
    public function setElementProperties(DataContainer $dc): void
    {
        if ($dc->activeRecord->type !== 'colsetStart'
            || $dc->activeRecord->sc_type === "")
        {
            return;
        }

        $endPart = $this->connection->fetchAssociative(
            "SELECT sorting FROM tl_content WHERE sc_name=?",
            [$dc->activeRecord->sc_name . '-End']
        );

        $stmt = $this->connection
            ->prepare("UPDATE tl_content SET protected=:protected, groups=:groups, guests=:guests WHERE pid=? AND sorting > ? AND sorting <= ?");
        $stmt->bindValue('protected', $dc->activeRecord->protected);
        $stmt->bindValue('groups', $dc->activeRecord->groups);
        $stmt->bindValue('guests', $dc->activeRecord->guests);
        $stmt->executeStatement([
            $dc->activeRecord->pid,
            $dc->activeRecord->sorting,
            $endPart['sorting']
        ]);
    }
}