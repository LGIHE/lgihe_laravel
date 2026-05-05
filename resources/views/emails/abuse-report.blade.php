<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abuse Report - {{ $report->report_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .report-id-banner {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            margin: 0;
        }
        .report-id-banner h2 {
            margin: 0 0 10px 0;
            color: #92400e;
            font-size: 20px;
        }
        .report-id-banner p {
            margin: 5px 0;
            color: #78350f;
        }
        .content {
            padding: 20px;
        }
        .section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section h3 {
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 5px;
        }
        .field {
            margin-bottom: 12px;
        }
        .field-label {
            font-weight: bold;
            color: #4b5563;
            display: block;
            margin-bottom: 4px;
        }
        .field-value {
            color: #1f2937;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .anonymous-notice {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: #991b1b;
        }
        .action-required {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .action-required h3 {
            margin-top: 0;
            color: #1e40af;
            border: none;
            padding: 0;
        }
        .action-required ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .action-required li {
            margin: 8px 0;
            color: #1e3a8a;
        }
        .confidentiality-notice {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            padding: 15px 20px;
            margin: 20px 0;
            font-size: 14px;
            color: #6b7280;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
        }
        .incident-type-badge {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🚨 CONFIDENTIAL ABUSE REPORT</h1>
        </div>

        <!-- Report ID Banner -->
        <div class="report-id-banner">
            <h2>Report ID: {{ $report->report_id }}</h2>
            <p><strong>Submission Date:</strong> {{ $report->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Report Type:</strong> <span class="incident-type-badge">{{ $incidentTypeDisplay }}</span></p>
        </div>

        <div class="content">
            @if($isAnonymous)
            <!-- Anonymous Report Notice -->
            <div class="anonymous-notice">
                <strong>⚠️ ANONYMOUS REPORT</strong><br>
                This report was submitted anonymously. No reporter contact information is available.
            </div>
            @else
            <!-- Reporter Information -->
            <div class="section">
                <h3>📋 Reporter Information</h3>
                
                @if($report->reporter_name)
                <div class="field">
                    <span class="field-label">Name:</span>
                    <span class="field-value">{{ $report->reporter_name }}</span>
                </div>
                @endif

                @if($report->reporter_email)
                <div class="field">
                    <span class="field-label">Email:</span>
                    <span class="field-value">{{ $report->reporter_email }}</span>
                </div>
                @endif

                @if($report->reporter_phone)
                <div class="field">
                    <span class="field-label">Phone:</span>
                    <span class="field-value">{{ $report->reporter_phone }}</span>
                </div>
                @endif

                @if($report->reporter_relationship)
                <div class="field">
                    <span class="field-label">Relationship to Incident:</span>
                    <span class="field-value">{{ ucfirst(str_replace('-', ' ', $report->reporter_relationship)) }}</span>
                </div>
                @endif

                @if($report->preferred_contact)
                <div class="field">
                    <span class="field-label">Preferred Contact Method:</span>
                    <span class="field-value">{{ ucfirst(str_replace('-', ' ', $report->preferred_contact)) }}</span>
                </div>
                @endif
            </div>
            @endif

            <!-- Incident Details -->
            <div class="section">
                <h3>🚨 Incident Details</h3>
                
                <div class="field">
                    <span class="field-label">Incident Type:</span>
                    <span class="field-value">{{ $incidentTypeDisplay }}</span>
                </div>

                <div class="field">
                    <span class="field-label">Date of Incident:</span>
                    <span class="field-value">{{ \Carbon\Carbon::parse($report->incident_date)->format('F j, Y') }}</span>
                </div>

                <div class="field">
                    <span class="field-label">Location:</span>
                    <span class="field-value">{{ $report->incident_location }}</span>
                </div>
            </div>

            <!-- Persons Involved -->
            <div class="section">
                <h3>👥 Persons Involved</h3>
                <div class="field-value">{{ $report->persons_involved }}</div>
            </div>

            <!-- Detailed Description -->
            <div class="section">
                <h3>📝 Detailed Description</h3>
                <div class="field-value">{{ $report->detailed_description }}</div>
            </div>

            @if($report->witnesses_present)
            <!-- Witnesses -->
            <div class="section">
                <h3>👁️ Witnesses Present</h3>
                <div class="field-value">{{ $report->witnesses_present }}</div>
            </div>
            @endif

            @if($report->previously_reported)
            <!-- Previous Reports -->
            <div class="section">
                <h3>📋 Previously Reported</h3>
                <div class="field-value">{{ $report->previously_reported }}</div>
            </div>
            @endif

            @if($report->evidence_available)
            <!-- Evidence -->
            <div class="section">
                <h3>📎 Evidence Available</h3>
                <div class="field-value">{{ $report->evidence_available }}</div>
            </div>
            @endif

            <!-- Action Required -->
            <div class="action-required">
                <h3>⚡ IMMEDIATE ACTION REQUIRED</h3>
                <p><strong>Safeguarding Team - Please take the following steps:</strong></p>
                <ul>
                    <li>Acknowledge receipt of this report within 24 hours</li>
                    <li>Assess the urgency and risk level of the incident</li>
                    <li>Initiate appropriate investigation procedures</li>
                    <li>Contact relevant authorities if required by policy</li>
                    <li>Document all actions taken in the case management system</li>
                    @if(!$isAnonymous && $report->preferred_contact !== 'no-contact')
                    <li>Contact the reporter using their preferred method within 48 hours</li>
                    @endif
                    <li>Ensure confidentiality is maintained throughout the process</li>
                </ul>
            </div>

            <!-- Confidentiality Notice -->
            <div class="confidentiality-notice">
                <strong>⚠️ CONFIDENTIALITY NOTICE</strong><br>
                This email contains confidential information related to an abuse report. This information is protected under institutional safeguarding policies and applicable data protection laws. Unauthorized disclosure, copying, distribution, or use of this information is strictly prohibited. If you received this email in error, please notify the sender immediately and delete this email.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>LGIHE Safeguarding Team</strong></p>
            <p>Email: safeguarding@lgihe.ac.ug</p>
            <p>Emergency: (+256) 414 222 517</p>
            <p style="margin-top: 15px; font-size: 12px;">
                This is an automated notification from the LGIHE Abuse Reporting System.<br>
                Report ID: {{ $report->report_id }} | Generated: {{ now()->format('Y-m-d H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>
