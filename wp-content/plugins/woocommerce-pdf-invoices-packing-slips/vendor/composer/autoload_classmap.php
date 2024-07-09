<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(__DIR__);
$baseDir = dirname($vendorDir);

return array(
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
    'Dompdf\\Cpdf' => $vendorDir . '/dompdf/dompdf/lib/Cpdf.php',
    'WPO\\WC\\PDF_Invoices\\Admin' => $baseDir . '/includes/class-wcpdf-admin.php',
    'WPO\\WC\\PDF_Invoices\\Assets' => $baseDir . '/includes/class-wcpdf-assets.php',
    'WPO\\WC\\PDF_Invoices\\Compatibility\\Order_Util' => $baseDir . '/includes/compatibility/class-wcpdf-order-util.php',
    'WPO\\WC\\PDF_Invoices\\Compatibility\\Third_Party_Plugins' => $baseDir . '/includes/compatibility/class-wcpdf-compatibility-third-party-plugins.php',
    'WPO\\WC\\PDF_Invoices\\Documents' => $baseDir . '/includes/class-wcpdf-documents.php',
    'WPO\\WC\\PDF_Invoices\\Documents\\Bulk_Document' => $baseDir . '/includes/documents/class-wcpdf-bulk-document.php',
    'WPO\\WC\\PDF_Invoices\\Documents\\Document_Number' => $baseDir . '/includes/documents/class-wcpdf-document-number.php',
    'WPO\\WC\\PDF_Invoices\\Documents\\Invoice' => $baseDir . '/includes/documents/class-wcpdf-invoice.php',
    'WPO\\WC\\PDF_Invoices\\Documents\\Order_Document' => $baseDir . '/includes/documents/abstract-wcpdf-order-document.php',
    'WPO\\WC\\PDF_Invoices\\Documents\\Order_Document_Methods' => $baseDir . '/includes/documents/abstract-wcpdf-order-document-methods.php',
    'WPO\\WC\\PDF_Invoices\\Documents\\Packing_Slip' => $baseDir . '/includes/documents/class-wcpdf-packing-slip.php',
    'WPO\\WC\\PDF_Invoices\\Documents\\Sequential_Number_Store' => $baseDir . '/includes/documents/class-wcpdf-sequential-number-store.php',
    'WPO\\WC\\PDF_Invoices\\Endpoint' => $baseDir . '/includes/class-wcpdf-endpoint.php',
    'WPO\\WC\\PDF_Invoices\\Font_Synchronizer' => $baseDir . '/includes/class-wcpdf-font-synchronizer.php',
    'WPO\\WC\\PDF_Invoices\\Frontend' => $baseDir . '/includes/class-wcpdf-frontend.php',
    'WPO\\WC\\PDF_Invoices\\Install' => $baseDir . '/includes/class-wcpdf-install.php',
    'WPO\\WC\\PDF_Invoices\\Main' => $baseDir . '/includes/class-wcpdf-main.php',
    'WPO\\WC\\PDF_Invoices\\Makers\\PDF_Maker' => $baseDir . '/includes/makers/class-pdf-maker.php',
    'WPO\\WC\\PDF_Invoices\\Makers\\UBL_Maker' => $baseDir . '/includes/makers/class-ubl-maker.php',
    'WPO\\WC\\PDF_Invoices\\Settings' => $baseDir . '/includes/class-wcpdf-settings.php',
    'WPO\\WC\\PDF_Invoices\\Settings\\Settings_Callbacks' => $baseDir . '/includes/settings/class-wcpdf-settings-callbacks.php',
    'WPO\\WC\\PDF_Invoices\\Settings\\Settings_Debug' => $baseDir . '/includes/settings/class-wcpdf-settings-debug.php',
    'WPO\\WC\\PDF_Invoices\\Settings\\Settings_Documents' => $baseDir . '/includes/settings/class-wcpdf-settings-documents.php',
    'WPO\\WC\\PDF_Invoices\\Settings\\Settings_General' => $baseDir . '/includes/settings/class-wcpdf-settings-general.php',
    'WPO\\WC\\PDF_Invoices\\Settings\\Settings_UBL' => $baseDir . '/includes/settings/class-wcpdf-settings-ubl.php',
    'WPO\\WC\\PDF_Invoices\\Settings\\Settings_Upgrade' => $baseDir . '/includes/settings/class-wcpdf-settings-upgrade.php',
    'WPO\\WC\\PDF_Invoices\\Setup_Wizard' => $baseDir . '/includes/class-wcpdf-setup-wizard.php',
    'WPO\\WC\\PDF_Invoices\\Tables\\Number_Store_List_Table' => $baseDir . '/includes/tables/class-wcpdf-number-store-list-table.php',
    'WPO\\WC\\PDF_Invoices\\Updraft_Semaphore_3_0' => $baseDir . '/includes/class-wcpdf-updraft-semaphore.php',
    'WPO\\WC\\UBL\\Builders\\Builder' => $baseDir . '/ubl/Builders/Builder.php',
    'WPO\\WC\\UBL\\Builders\\SabreBuilder' => $baseDir . '/ubl/Builders/SabreBuilder.php',
    'WPO\\WC\\UBL\\Collections\\Collection' => $baseDir . '/ubl/Collections/Collection.php',
    'WPO\\WC\\UBL\\Collections\\OrderCollection' => $baseDir . '/ubl/Collections/OrderCollection.php',
    'WPO\\WC\\UBL\\Documents\\Document' => $baseDir . '/ubl/Documents/Document.php',
    'WPO\\WC\\UBL\\Documents\\UblDocument' => $baseDir . '/ubl/Documents/UblDocument.php',
    'WPO\\WC\\UBL\\Exceptions\\FileWriteException' => $baseDir . '/ubl/Exceptions/FileWriteException.php',
    'WPO\\WC\\UBL\\Handlers\\Handler' => $baseDir . '/ubl/Handlers/Handler.php',
    'WPO\\WC\\UBL\\Handlers\\UblHandler' => $baseDir . '/ubl/Handlers/UblHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\AdditionalDocumentReferenceHandler' => $baseDir . '/ubl/Handlers/Ubl/AdditionalDocumentReferenceHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\AddressHandler' => $baseDir . '/ubl/Handlers/Ubl/AddressHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\AllowanceChargeHandler' => $baseDir . '/ubl/Handlers/Ubl/AllowanceChargeHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\DeliveryHandler' => $baseDir . '/ubl/Handlers/Ubl/DeliveryHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\DocumentCurrencyCodeHandler' => $baseDir . '/ubl/Handlers/Ubl/DocumentCurrencyCodeHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\IdHandler' => $baseDir . '/ubl/Handlers/Ubl/IdHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\InvoiceLineHandler' => $baseDir . '/ubl/Handlers/Ubl/InvoiceLineHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\InvoiceTypeCodeHandler' => $baseDir . '/ubl/Handlers/Ubl/InvoiceTypeCodeHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\IssueDateHandler' => $baseDir . '/ubl/Handlers/Ubl/IssueDateHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\LegalMonetaryTotalHandler' => $baseDir . '/ubl/Handlers/Ubl/LegalMonetaryTotalHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\OrderReferenceHandler' => $baseDir . '/ubl/Handlers/Ubl/OrderReferenceHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\PaymentMeansHandler' => $baseDir . '/ubl/Handlers/Ubl/PaymentMeansHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\PaymentTermsHandler' => $baseDir . '/ubl/Handlers/Ubl/PaymentTermsHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\TaxTotalHandler' => $baseDir . '/ubl/Handlers/Ubl/TaxTotalHandler.php',
    'WPO\\WC\\UBL\\Handlers\\Ubl\\UblVersionIdHandler' => $baseDir . '/ubl/Handlers/Ubl/UblVersionIdHandler.php',
    'WPO\\WC\\UBL\\Models\\Address' => $baseDir . '/ubl/Models/Address.php',
    'WPO\\WC\\UBL\\Models\\DateTime' => $baseDir . '/ubl/Models/DateTime.php',
    'WPO\\WC\\UBL\\Models\\Model' => $baseDir . '/ubl/Models/Model.php',
    'WPO\\WC\\UBL\\Models\\Order' => $baseDir . '/ubl/Models/Order.php',
    'WPO\\WC\\UBL\\Repositories\\Contracts\\OrderRepository' => $baseDir . '/ubl/Repositories/Contracts/OrderRepository.php',
    'WPO\\WC\\UBL\\Repositories\\OrderRepository' => $baseDir . '/ubl/Repositories/OrderRepository.php',
    'WPO\\WC\\UBL\\Repositories\\Repository' => $baseDir . '/ubl/Repositories/Repository.php',
    'WPO\\WC\\UBL\\Settings\\TaxesSettings' => $baseDir . '/ubl/Settings/TaxesSettings.php',
    'WPO\\WC\\UBL\\Transformers\\AddressTransformer' => $baseDir . '/ubl/Transformers/AddressTransformer.php',
    'WPO\\WC\\UBL\\Transformers\\DateTimeTransformer' => $baseDir . '/ubl/Transformers/DateTimeTransformer.php',
    'WPO\\WC\\UBL\\Transformers\\OrderTransformer' => $baseDir . '/ubl/Transformers/OrderTransformer.php',
);