<?php

namespace Database\Seeders;

use App\Models\FormOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $centers = [
            "National (Central Office)",
            "North Luzon",
            "South Luzon",
            "Central Visayas",
            "Western Visayas",
            "North Mindanao",
            "South Mindanao"
        ];

        foreach ($centers as $index => $center) {
            FormOption::create([
                'category' => 'center',
                'label' => $center,
                'sort_order' => $index + 1,
            ]);
        }

        $regions = [
            "Region I - Ilocos Region",
            "Region II - Cagayan Valley",
            "Region III - Central Luzon",
            "Region IV - A - CALABARZON",
            "Region IV - B - MIMAROPA",
            "Region V - Bicol Region",
            "Region VI - Western Visayas",
            "Region VII - Central Visayas",
            "Region VIII - Eastern Visayas",
            "Region IX - Zamboanga Peninsula",
            "Region X - Northern Mindanao",
            "Region XI - Davao Region",
            "Region XII - SOCCSKSARGEN",
            "Region XIII - Caraga",
            "NCR - National Capital Region",
            "CAR - Cordillera Administrative Region",
            "BARMM - Bangsamoro Autonomous Region in Muslim Mindanao"
        ];

        foreach ($regions as $index => $region) {
            FormOption::create([
                'category' => 'region',
                'label' => $region,
                'sort_order' => $index + 1,
            ]);
        }

        $services = [
            "Animal Breeding Services - Dairy Herd (Natural Heat)",
            "Animal Breeding Services - Request For Breeding Services (Synchronized/ Fixed-Time Artificial Insemination) - For 10 Heads And Below",
            "Animal Breeding Services - Upgrading",
            "Availment of Conference Room",
            "Availment of Dormitory",
            "Collections thru Cash / Check",
            "Collections thru Deposit",
            "Disbursement and Issuance of Check",
            "Emergency Animal Health Services",
            "Facilitation of Local Dairy Animals Buy-back Services",
            "Formulation of Agency Strategic Plans and Programs",
            "Information and Technology Services",
            "Issuance and Release of Check",
            "Issuance of Supplies",
            "Loan of Dairy Animals (Sourced from Importation) - Conduct of Final Evaluation (If Imported Animals)",
            "Loan of Dairy Animals (Sourced from Importation) - Conduct of Orientation and Initial Evaluation",
            "Loan of Dairy Animals (Sourced from Importation) - Distribution of Animals",
            "Loan of Dairy Animals (Sourced from Payments-in-Kind) - Conduct of Final Evaluation (If Local Animals)",
            "Loan of Dairy Animals (Sourced from Payments-in-Kind) - Conduct of Orientation and Initial Evaluation",
            "Loan of Dairy Animals (Sourced from Payments-in-Kind) - Distribution of Animals",
            "Local Dairy Industry Monitoring",
            "Milk Feeding Program Activities",
            "Milk Testing Services (Micro-biological Analyses)",
            "Milk Testing Services (Physico-Chemical Analyses)",
            "Milk Testing Services (Udder Health Services)",
            "Procurement of Goods and Services for ABC amounting to PHP1M and up",
            "Procurement of Goods and Services for ABC amounting to PhP50,000 and below",
            "Procurement of Goods and Services for ABC amounting to PhP50,000 to PhP1M",
            "Product Development Service",
            "Program/Project Monitoring and Evaluation",
            "Project Proposal Packaging",
            "Provision of Training Services (Client-Initiated)",
            "Provision of Vehicles for Official Business",
            "Registration and Licensing of DBOs (Application for NDA LTO)",
            "Registration and Licensing of DBOs (Registration of DBOs)",
            "Request for Certifications",
            "Request for Certifications Related to Philippine Dairy Corporation (PDC)",
            "Request for Personnel Documents",
            "Request for Personnel Documents related to Philippine Dairy Corporation (PDC)",
            "Request for Repairs and/or Maintenance",
            "Request for Retrieval of Documents/Records from Years 2022 and Earlier",
            "Request for Retrieval of Documents/Records of the Current Year",
            "Requests for Liaison/Messenger Services (Transmittal/Pick-Up Of Documents/Cargo)",
            "Transfer of Funds to Regional Dairy Farmers Livelihood Center (RDFLC)",
            "Transmittal of Documents Thru Courier Services",
            "Vehicle Dispatching",
            "Website Publication"
        ];

        foreach ($services as $index => $service) {
            FormOption::create([
                'category' => 'service',
                'label' => $service,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
