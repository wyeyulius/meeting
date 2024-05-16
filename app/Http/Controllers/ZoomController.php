<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Jubaer\Zoom\Facades\Zoom;
use Inertia\Inertia;
use App\Models\Zoom as ZoomModel;

class ZoomController extends Controller
{
    /**
     * Display dashboard.
     */
    public function dashboard()
    {
        $upcoming_meetings = Zoom::getUpcomingMeeting();
        $previous_meetings = Zoom::getPreviousMeeting();
        $live_meetings = Zoom::getLiveMeeting();
        $all_meetings = Zoom::getAllMeeting();

        $meetings = ZoomModel::with('user')->orderBy('created_at', 'desc')->get();
        
        // dd($all_meetings, $upcoming_meetings, $previous_meetings, $live_meetings);
    
        return Inertia::render('Dashboard', [
            'meetings' => $meetings,
            'all_meetings' => $all_meetings,
            'upcoming_meetings' => $upcoming_meetings,
            'previous_meetings' => $previous_meetings,
            'live_meetings' => $live_meetings,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(String $id = null)
    {
        $user_meetings = ZoomModel::where('user_id', auth()->user()->id)->get();

        if ($id !== null) {
            $meeting = Zoom::getMeeting($id);
            $invitation = Zoom::getMeetingInvitation($id);
            // dd($meeting, $invitation);
        }

        return Inertia::render('Zoom/Index', [
            'user_meetings' => $user_meetings,
            'meeting' => $meeting ?? null,
            'invitation' => $invitation ?? null,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $type = 2;
        $start_date = $request->start_date['year'] . '-' . $request->start_date['month'] . '-' . $request->start_date['day'];
        $end_date = null;
        if ($request->end_date) {
            $end_date = $request->end_date['year'] . '-' . $request->end_date['month'] . '-' . $request->end_date['day'];
            $type = 8;
        }
        // dd($start_date,$end_date,$type);
        
        if ($request->end_date) {
            $start_date_string = $request->start_date['year'] . '-' . $request->start_date['month'] . '-' . $request->start_date['day'];
            $end_date_string = $request->end_date['year'] . '-' . $request->end_date['month'] . '-' . $request->end_date['day'];

            $start_date = new \DateTime($start_date_string . ' ' . $request->time . ' ' . $request->period, new \DateTimeZone('Asia/Singapore'));
            $end_date = new \DateTime($end_date_string . ' ' . $request->time . ' ' . $request->period, new \DateTimeZone('Asia/Singapore'));

            $start_date->setTimezone(new \DateTimeZone('UTC'));
            $end_date->setTimezone(new \DateTimeZone('UTC'));

            $upcoming_meetings = Zoom::getUpcomingMeeting();

            for ($date = clone $start_date; $date <= $end_date; $date->modify('+1 day')) {
                $start_time = $date->format('Y-m-d\TH:i:s');
                $end_time = (clone $date)->modify('+' . $request->duration * 60 . ' minutes')->format('Y-m-d\TH:i:s');

                foreach ($upcoming_meetings['data']['meetings'] as $meeting) {
                    $meeting_start_time = new \DateTime($meeting['start_time'], new \DateTimeZone('UTC'));
                    $meeting_start_time = $meeting_start_time->format('Y-m-d\TH:i:s');

                    $meeting_end_time = new \DateTime($meeting_start_time . ' + ' . $meeting['duration'] . ' minutes', new \DateTimeZone('UTC'));
                    $meeting_end_time = $meeting_end_time->format('Y-m-d\TH:i:s');

                    if (($start_time >= $meeting_start_time && $start_time < $meeting_end_time) ||
                        ($end_time > $meeting_start_time && $end_time <= $meeting_end_time) ||
                        ($start_time <= $meeting_start_time && $end_time >= $meeting_end_time)) {
                        // The new meeting falls within the time range of an existing meeting
                        return Redirect::route('zoom.index')->with('error', 'Gagal membuat meeting karena sudah ada zoom di waktu yang sama');
                    }
                }
            }

        } else {
            $start_time = new \DateTime($start_date . ' ' . $request->time . ' ' . $request->period, new \DateTimeZone('Asia/Singapore'));
            $start_time->setTimezone(new \DateTimeZone('UTC'));
            $start_time = $start_time->format('Y-m-d\TH:i:s');

            $end_time = new \DateTime($start_time . ' + ' . $request->duration * 60 . ' minutes', new \DateTimeZone('UTC'));
            $end_time = $end_time->format('Y-m-d\TH:i:s');

            $upcoming_meetings = Zoom::getUpcomingMeeting();

            foreach ($upcoming_meetings['data']['meetings'] as $meeting) {
                $meeting_start_time = new \DateTime($meeting['start_time'], new \DateTimeZone('UTC'));
                $meeting_start_time = $meeting_start_time->format('Y-m-d\TH:i:s');

                $meeting_end_time = new \DateTime($meeting_start_time . ' + ' . $meeting['duration'] . ' minutes', new \DateTimeZone('UTC'));
                $meeting_end_time = $meeting_end_time->format('Y-m-d\TH:i:s');

                if (($start_time >= $meeting_start_time && $start_time < $meeting_end_time) ||
                    ($end_time > $meeting_start_time && $end_time <= $meeting_end_time) ||
                    ($start_time <= $meeting_start_time && $end_time >= $meeting_end_time)) {
                    // The new meeting falls within the time range of an existing meeting
                    return Redirect::route('zoom.index')->with('error', 'Gagal membuat meeting karena sudah ada zoom di waktu yang sama');
                }
            }
        } 

        $start_date = $request->start_date['year'] . '-' . $request->start_date['month'] . '-' . $request->start_date['day'];
        $recurrence = 0;
        if ($end_date !== null) {
            $end_date = $request->end_date['year'] . '-' . $request->end_date['month'] . '-' . $request->end_date['day'];
            $start = strtotime($start_date);
            $end = strtotime($end_date);
            $recurrence = floor(($end - $start) / (60 * 60 * 24)) + 1;
        }
        // dd($recurrence);
        $start_time = date('Y-m-d\TH:i:s', strtotime($start_date . ' ' . $request->time . ' ' . $request->period));
        if ($type == 8) {
            $meetings = Zoom::createMeeting([
                "agenda" => $request->topic,
                "topic" => $request->topic,
                "type" => $type, // 1 => instant, 2 => scheduled, 3 => recurring with no fixed time, 8 => recurring with fixed time
                "duration" => $request->duration*60, // in minutes
                "password" => $request->password,
                "timezone" => 'Asia/Singapore', // set your timezone
                "start_time" => $start_time, // set your start time
                "recurrence" => [
                    'type' => 1, // 1 => Daily, 2 => Weekly, 3 => Monthly, 4 => Yearly
                    'repeat_interval' => 1, // set your repeat interval
                    'end_times' => $recurrence, // set your end times
                ],
                "settings" => [
                    'join_before_host' => true, // if you want to join before host set true otherwise set false
                    'host_video' => false, // if you want to start video when host join set true otherwise set false
                    'participant_video' => false, // if you want to start video when participants join set true otherwise set false
                    'mute_upon_entry' => true, // if you want to mute participants when they join the meeting set true otherwise set false
                    'waiting_room' => true, // if you want to use waiting room for participants set true otherwise set false
                    'audio' => 'both', // values are 'both', 'telephony', 'voip'. default is both.
                    'auto_recording' => 'none', // values are 'none', 'local', 'cloud'. default is none.
                    'approval_type' => 2, // 0 => Automatically Approve, 1 => Manually Approve, 2 => No Registration Required
                ],
            
            ]);
        }
        else{
            $meetings = Zoom::createMeeting([
                "agenda" => $request->topic,
                "topic" => $request->topic,
                "type" => $type, // 1 => instant, 2 => scheduled, 3 => recurring with no fixed time, 8 => recurring with fixed time
                "duration" => $request->duration*60, // in minutes
                "password" => $request->password,
                "timezone" => 'Asia/Singapore', // set your timezone
                "start_time" => $start_time, // set your start time
                // "template_id" => 'set your template id', // set your template id  Ex: "Dv4YdINdTk+Z5RToadh5ug==" from https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingtemplates
                // "pre_schedule" => false,  // set true if you want to create a pre-scheduled meeting
                // "schedule_for" => 'set your schedule for profile email ', // set your schedule for
                "settings" => [
                    'join_before_host' => true, // if you want to join before host set true otherwise set false
                    'host_video' => false, // if you want to start video when host join set true otherwise set false
                    'participant_video' => false, // if you want to start video when participants join set true otherwise set false
                    'mute_upon_entry' => true, // if you want to mute participants when they join the meeting set true otherwise set false
                    'waiting_room' => true, // if you want to use waiting room for participants set true otherwise set false
                    'audio' => 'both', // values are 'both', 'telephony', 'voip'. default is both.
                    'auto_recording' => 'none', // values are 'none', 'local', 'cloud'. default is none.
                    'approval_type' => 2, // 0 => Automatically Approve, 1 => Manually Approve, 2 => No Registration Required
                ],
            
            ]);
        }
        if ($meetings['status'] == true) {
            $meeting = new ZoomModel();
            $meeting->user_id = auth()->user()->id;
            $meeting->meeting_id = $meetings['data']['id'];
            $meeting->topic = $meetings['data']['topic'];
            $meeting->jumlah_peserta = $request->participant;
            $meeting->bidang = $request->bidang;
            $meeting->co_host = $request->host;
            $meeting->start_date = $start_date;
            $meeting->end_date = $end_date;
            $meeting->time = $request->time;
            $meeting->period = $request->period;
            $meeting->duration = $request->duration;
            $meeting->save();
            return Redirect::route('zoom.index')->with('success', 'Meeting telah dibuat');
        } else {
            return Redirect::route('zoom.index')->with('error', 'Gagal membuat meeting');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $meeting = ZoomModel::where('meeting_id', $id)->first();
        $delete = Zoom::deleteMeeting($id);
        if ($delete['status'] == true) {
            $meeting->delete();
            return Redirect::route('zoom.index')->with('success', 'Meeting berhasil dihapus');
        } else {
            return Redirect::route('zoom.index')->with('error', 'Gagal menghapus meeting');
        }
    }
}
