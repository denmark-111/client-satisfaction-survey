<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['code' => 'SRV-AB-NATURAL-HEAT', 'name' => 'Animal Breeding Services - Dairy Herd (Natural Heat)'],
            ['code' => 'SRV-AB-SYNC-AI', 'name' => 'Animal Breeding Services - Request For Breeding Services (Synchronized/ Fixed-Time Artificial Insemination) - For 10 Heads And Below'],
            ['code' => 'SRV-AB-UPGRADING', 'name' => 'Animal Breeding Services - Upgrading'],
            ['code' => 'SRV-CONF-ROOM', 'name' => 'Availment of Conference Room'],
            ['code' => 'SRV-DORMITORY', 'name' => 'Availment of Dormitory'],
            ['code' => 'SRV-COLL-CASH-CHECK', 'name' => 'Collections thru Cash / Check'],
            ['code' => 'SRV-COLL-DEPOSIT', 'name' => 'Collections thru Deposit'],
            ['code' => 'SRV-DISBURSE-CHECK', 'name' => 'Disbursement and Issuance of Check'],
            ['code' => 'SRV-EMERGENCY-ANIMAL-HEALTH', 'name' => 'Emergency Animal Health Services'],
            ['code' => 'SRV-BUYBACK-SERVICES', 'name' => 'Facilitation of Local Dairy Animals Buy-back Services'],
            ['code' => 'SRV-STRATEGIC-PLANS', 'name' => 'Formulation of Agency Strategic Plans and Programs'],
            ['code' => 'SRV-IT-SERVICES', 'name' => 'Information and Technology Services'],
            ['code' => 'SRV-RELEASE-CHECK', 'name' => 'Issuance and Release of Check'],
            ['code' => 'SRV-ISSUANCE-SUPPLIES', 'name' => 'Issuance of Supplies'],
            ['code' => 'SRV-LOAN-IMPORT-FINAL', 'name' => 'Loan of Dairy Animals (Sourced from Importation) - Conduct of Final Evaluation (If Imported Animals)'],
            ['code' => 'SRV-LOAN-IMPORT-INIT', 'name' => 'Loan of Dairy Animals (Sourced from Importation) - Conduct of Orientation and Initial Evaluation'],
            ['code' => 'SRV-LOAN-IMPORT-DIST', 'name' => 'Loan of Dairy Animals (Sourced from Importation) - Distribution of Animals'],
            ['code' => 'SRV-LOAN-KIND-FINAL', 'name' => 'Loan of Dairy Animals (Sourced from Payments-in-Kind) - Conduct of Final Evaluation (If Local Animals)'],
            ['code' => 'SRV-LOAN-KIND-INIT', 'name' => 'Loan of Dairy Animals (Sourced from Payments-in-Kind) - Conduct of Orientation and Initial Evaluation'],
            ['code' => 'SRV-LOAN-KIND-DIST', 'name' => 'Loan of Dairy Animals (Sourced from Payments-in-Kind) - Distribution of Animals'],
            ['code' => 'SRV-INDUSTRY-MONITORING', 'name' => 'Local Dairy Industry Monitoring'],
            ['code' => 'SRV-MILK-FEEDING-PROG', 'name' => 'Milk Feeding Program Activities'],
            ['code' => 'SRV-MILK-TEST-MICRO', 'name' => 'Milk Testing Services (Micro-biological Analyses)'],
            ['code' => 'SRV-MILK-TEST-PHYS', 'name' => 'Milk Testing Services (Physico-Chemical Analyses)'],
            ['code' => 'SRV-MILK-TEST-UDDER', 'name' => 'Milk Testing Services (Udder Health Services)'],
            ['code' => 'SRV-PROC-ABC-1M-UP', 'name' => 'Procurement of Goods and Services for ABC amounting to PHP1M and up'],
            ['code' => 'SRV-PROC-ABC-50K-BELOW', 'name' => 'Procurement of Goods and Services for ABC amounting to PhP50,000 and below'],
            ['code' => 'SRV-PROC-ABC-50K-1M', 'name' => 'Procurement of Goods and Services for ABC amounting to PhP50,000 to PhP1M'],
            ['code' => 'SRV-PROD-DEV', 'name' => 'Product Development Service'],
            ['code' => 'SRV-PROG-MONITOR-EVAL', 'name' => 'Program/Project Monitoring and Evaluation'],
            ['code' => 'SRV-PROJ-PACKAGING', 'name' => 'Project Proposal Packaging'],
            ['code' => 'SRV-TRAINING-CLIENT', 'name' => 'Provision of Training Services (Client-Initiated)'],
            ['code' => 'SRV-VEHICLES-OFFICIAL', 'name' => 'Provision of Vehicles for Official Business'],
            ['code' => 'SRV-DBO-LTO-APP', 'name' => 'Registration and Licensing of DBOs (Application for NDA LTO)'],
            ['code' => 'SRV-DBO-REGISTRATION', 'name' => 'Registration and Licensing of DBOs (Registration of DBOs)'],
            ['code' => 'SRV-REQ-CERTIFICATIONS', 'name' => 'Request for Certifications'],
            ['code' => 'SRV-REQ-CERT-PDC', 'name' => 'Request for Certifications Related to Philippine Dairy Corporation (PDC)'],
            ['code' => 'SRV-REQ-PERSONNEL-DOCS', 'name' => 'Request for Personnel Documents'],
            ['code' => 'SRV-REQ-PERS-DOCS-PDC', 'name' => 'Request for Personnel Documents related to Philippine Dairy Corporation (PDC)'],
            ['code' => 'SRV-REQ-REPAIRS-MAINT', 'name' => 'Request for Repairs and/or Maintenance'],
            ['code' => 'SRV-REQ-RECORDS-2022-EARLIER', 'name' => 'Request for Retrieval of Documents/Records from Years 2022 and Earlier'],
            ['code' => 'SRV-REQ-RECORDS-CURRENT', 'name' => 'Request for Retrieval of Documents/Records of the Current Year'],
            ['code' => 'SRV-REQ-MESSENGER-SERVICES', 'name' => 'Requests for Liaison/Messenger Services (Transmittal/Pick-Up Of Documents/Cargo)'],
            ['code' => 'SRV-TRANSFER-FUNDS-RDFLC', 'name' => 'Transfer of Funds to Regional Dairy Farmers Livelihood Center (RDFLC)'],
            ['code' => 'SRV-COURIER-SERVICES', 'name' => 'Transmittal of Documents Thru Courier Services'],
            ['code' => 'SRV-VEHICLE-DISPATCH', 'name' => 'Vehicle Dispatching'],
            ['code' => 'SRV-WEBSITE-PUBLICATION', 'name' => 'Website Publication'],
        ];

        foreach ($services as $index => $service) {
            Service::updateOrCreate(
                ['code' => $service['code']],
                [
                    'name' => $service['name'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
