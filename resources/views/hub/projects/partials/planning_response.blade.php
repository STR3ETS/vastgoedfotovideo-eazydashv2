@include('hub.projects.partials.planning', [
  'project' => $project,
  'planningErrors' => $planningErrors ?? null,
  'planningAssignees' => $planningAssignees ?? collect(),
  'sectionWrap' => $sectionWrap ?? "overflow-hidden rounded-2xl",
  'sectionHeader' => $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10",
  'sectionBody' => $sectionBody ?? "bg-[#191D38]/5",
])

@include('hub.projects.partials.logbook', [
  'project' => $project,
  'oob' => true,
  'sectionWrap' => $sectionWrap ?? "overflow-hidden rounded-2xl",
  'sectionHeader' => $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10",
  'sectionBody' => $sectionBody ?? "bg-[#191D38]/5",
])
