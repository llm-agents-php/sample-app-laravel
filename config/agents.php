<?php

declare(strict_types=1);


use App\Agents\CodeReviewer\CodeReviewAgentFactory;
use App\Agents\Delivery\DeliveryAgentFactory;
use App\Agents\SmartHomeControl\SmartHomeControlAgentFactory;
use App\Agents\TaskSplitter\TaskSplitterAgentFactory;
use LLM\Agents\Agent\SiteStatusChecker;

return [
    'agents' => [
//        CodeReviewAgentFactory::class,
        DeliveryAgentFactory::class,
        SmartHomeControlAgentFactory::class,
        TaskSplitterAgentFactory::class,
        SiteStatusChecker\SiteStatusCheckerAgentFactory::class,
    ],
    'tools' => [
        \App\Agents\AgentsCaller\AskAgentTool::class,

        // Code Reviewer
//        \App\Agents\CodeReviewer\ListProjectTool::class,
//        \App\Agents\CodeReviewer\ReadFileTool::class,
//        \App\Agents\CodeReviewer\ReviewTool::class,

        // Delivery
        \App\Agents\Delivery\GetOrderNumberTool::class,
        \App\Agents\Delivery\GetDeliveryDateTool::class,
        \App\Agents\Delivery\GetOrderNumberTool::class,
        \App\Agents\Delivery\GetProfileTool::class,

        // Dynamic memory
        \App\Agents\DynamicMemoryTool\DynamicMemoryTool::class,


        // Smart Home Control
        \App\Agents\SmartHomeControl\ControlDeviceTool::class,
        \App\Agents\SmartHomeControl\GetDeviceDetailsTool::class,
        \App\Agents\SmartHomeControl\GetRoomListTool::class,
        \App\Agents\SmartHomeControl\ListRoomDevicesTool::class,

        // Task splitter
        \App\Agents\TaskSplitter\TaskCreateTool::class,
        \App\Agents\TaskSplitter\GetProjectDescription::class,

        // Site Status Checker
        SiteStatusChecker\CheckSiteAvailabilityTool::class,
        SiteStatusChecker\GetDNSInfoTool::class,
        SiteStatusChecker\PerformPingTestTool::class,
    ],
];
