<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PaketBeasiswa;
use App\Models\User;

/**
 * Central hub for all in-app notification creation.
 * Each static method creates a record in the `notifications` collection
 * for the target user (student or mentor).
 *
 * Types used:
 *  - new_chat          → new chat message (group or private)
 *  - new_task          → mentor published a new task
 *  - task_deadline     → task approaching deadline
 *  - task_graded       → mentor graded / reviewed a submission
 *  - task_submitted    → student submitted a task (for mentor)
 *  - new_mentoring     → mentor published a new mentoring session
 *  - checkpoint_in     → student submitted a checkpoint (for mentor)
 *  - checkpoint_ok     → mentor verified a checkpoint (for student)
 *  - graduation_in     → student submitted graduation proof (for mentor)
 *  - graduation_result → mentor decided lulus/gagal (for student)
 *  - new_testimonial   → student submitted a testimonial (for mentor)
 */
class NotificationService
{
    // ── Core helper ──────────────────────────────────────────────────────────

    private static function create(string $userId, string $type, string $title, string $body, ?string $referenceId = null): void
    {
        Notification::create([
            'user_id'      => $userId,
            'type'         => $type,
            'title'        => $title,
            'body'         => $body,
            'is_read'      => false,
            'reference_id' => $referenceId,
        ]);
    }

    // ── Lookup helpers ───────────────────────────────────────────────────────

    /**
     * Get all student User models enrolled in a beasiswa by name.
     * Matches both array-stored and JSON-string-stored beasiswa_diampu.
     */
    private static function getStudentsByBeasiswaName(string $beasiswaName): \Illuminate\Support\Collection
    {
        $escaped = preg_quote($beasiswaName, '/');
        return User::where('role', 'student')
            ->where('beasiswa_diampu', 'regex', "/{$escaped}/i")
            ->get();
    }

    /**
     * Get all mentor User models assigned to a beasiswa by name.
     */
    private static function getMentorsByBeasiswaName(string $beasiswaName): \Illuminate\Support\Collection
    {
        $escaped = preg_quote($beasiswaName, '/');
        return User::where('role', 'mentor')
            ->where('beasiswa_diampu', 'regex', "/{$escaped}/i")
            ->get();
    }

    /**
     * Resolve beasiswa name from a PaketBeasiswa class_id (MongoDB ObjectId).
     */
    private static function resolveBeasiswaName(string $classId): ?string
    {
        return PaketBeasiswa::find($classId)?->nama_beasiswa;
    }

    // ── CHAT ─────────────────────────────────────────────────────────────────

    /**
     * Notify a user they have a new chat message.
     * @param string $targetUserId  The user who should receive the notification
     * @param string $senderName    Display name of the sender
     * @param string $roomName      Name of the chat room (group) or 'Pesan Pribadi'
     * @param string $preview       First ~60 chars of the message
     * @param string $roomId        Reference room ID
     */
    public static function newChat(string $targetUserId, string $senderName, string $roomName, string $preview, string $roomId): void
    {
        self::create(
            $targetUserId,
            'new_chat',
            "💬 Pesan baru dari {$senderName}",
            "[{$roomName}] {$preview}",
            $roomId
        );
    }

    // ── TASK (Student) ───────────────────────────────────────────────────────

    /**
     * Notify a single student that a new task has been published.
     */
    public static function newTask(string $studentId, string $taskTitle, string $taskId): void
    {
        self::create(
            $studentId,
            'new_task',
            '📋 Penugasan Baru',
            "Penugasan baru telah ditambahkan: \"{$taskTitle}\". Segera kerjakan sebelum deadline!",
            $taskId
        );
    }

    /**
     * Broadcast new task notification to ALL students enrolled in this class.
     * Resolves students via PaketBeasiswa name → User.beasiswa_diampu.
     */
    public static function broadcastNewTask(string $classId, string $taskTitle, string $taskId): void
    {
        $beasiswaName = self::resolveBeasiswaName($classId);
        if (! $beasiswaName) return;

        foreach (self::getStudentsByBeasiswaName($beasiswaName) as $student) {
            self::newTask((string) $student->_id, $taskTitle, $taskId);
        }
    }

    /**
     * Notify student that their task deadline is approaching.
     */
    public static function taskDeadlineSoon(string $studentId, string $taskTitle, string $deadline, string $taskId): void
    {
        self::create(
            $studentId,
            'task_deadline',
            '⏰ Deadline Mendekat',
            "Penugasan \"{$taskTitle}\" akan berakhir pada {$deadline}. Segera kumpulkan!",
            $taskId
        );
    }

    /**
     * Notify student their submission has been graded/reviewed by mentor.
     */
    public static function submissionGraded(string $studentId, string $taskTitle, string $feedback, string $submissionId): void
    {
        $body = "Tugasmu \"{$taskTitle}\" telah dikoreksi oleh mentor.";
        if (! empty($feedback)) {
            $body .= " Catatan: {$feedback}";
        }
        self::create($studentId, 'task_graded', '✅ Tugas Dikoreksi', $body, $submissionId);
    }

    // ── MENTORING (Student) ──────────────────────────────────────────────────

    /**
     * Notify student(s) that a new mentoring session has been scheduled.
     */
    public static function newMentoring(string $studentId, string $sessionTitle, string $sessionDate, string $sessionId): void
    {
        self::create(
            $studentId,
            'new_mentoring',
            '📅 Sesi Mentoring Baru',
            "Sesi mentoring \"{$sessionTitle}\" dijadwalkan pada {$sessionDate}. Jangan lupa hadir!",
            $sessionId
        );
    }

    /**
     * Broadcast new mentoring session to ALL students in the class.
     */
    public static function broadcastNewMentoring(string $classId, string $sessionTitle, string $sessionDate, string $sessionId): void
    {
        $beasiswaName = self::resolveBeasiswaName($classId);
        if (! $beasiswaName) return;

        foreach (self::getStudentsByBeasiswaName($beasiswaName) as $student) {
            self::newMentoring((string) $student->_id, $sessionTitle, $sessionDate, $sessionId);
        }
    }

    // ── CHECKPOINT ───────────────────────────────────────────────────────────

    /**
     * Notify mentor that a student has submitted a checkpoint.
     */
    public static function checkpointSubmitted(string $mentorId, string $studentName, string $checkpointTitle, string $submissionId): void
    {
        self::create(
            $mentorId,
            'checkpoint_in',
            '📌 Checkpoint Masuk',
            "{$studentName} telah mengirimkan bukti checkpoint: \"{$checkpointTitle}\". Silakan periksa.",
            $submissionId
        );
    }

    /**
     * Broadcast checkpoint submission to ALL mentors of that beasiswa.
     * @param string $beasiswaName  Direct beasiswa name (already resolved)
     */
    public static function broadcastCheckpointToMentors(string $beasiswaName, string $studentName, string $checkpointTitle, string $submissionId): void
    {
        foreach (self::getMentorsByBeasiswaName($beasiswaName) as $mentor) {
            self::checkpointSubmitted((string) $mentor->_id, $studentName, $checkpointTitle, $submissionId);
        }
    }

    /**
     * Notify student that their checkpoint has been verified by mentor.
     */
    public static function checkpointVerified(string $studentId, string $checkpointTitle, bool $approved): void
    {
        $icon   = $approved ? '✅' : '❌';
        $status = $approved ? 'disetujui' : 'ditolak';
        self::create(
            $studentId,
            'checkpoint_ok',
            "{$icon} Checkpoint {$status}",
            "Checkpoint \"{$checkpointTitle}\" kamu telah {$status} oleh mentor.",
            null
        );
    }

    // ── GRADUATION ───────────────────────────────────────────────────────────

    /**
     * Notify mentor that a student has submitted graduation proof.
     */
    public static function graduationSubmitted(string $mentorId, string $studentName, string $beasiswaName, string $studentId): void
    {
        self::create(
            $mentorId,
            'graduation_in',
            '🎓 Bukti Kelulusan Masuk',
            "{$studentName} telah mengirimkan bukti kelulusan untuk beasiswa \"{$beasiswaName}\". Silakan verifikasi.",
            $studentId
        );
    }

    /**
     * Notify student of their graduation result (lulus / gagal).
     */
    public static function graduationResult(string $studentId, string $beasiswaName, string $status): void
    {
        $lulus = $status === 'lulus';
        $icon  = $lulus ? '🎉' : '😔';
        $title = $lulus ? "{$icon} Selamat! Kamu Lulus" : "{$icon} Hasil Kelulusan";
        $body  = $lulus
            ? "Selamat! Kamu telah dinyatakan LULUS dari program beasiswa \"{$beasiswaName}\". Terimakasih atas dedikasi kamu!"
            : "Kamu dinyatakan TIDAK LULUS dari program beasiswa \"{$beasiswaName}\". Jangan menyerah, terus tingkatkan kemampuanmu!";
        self::create($studentId, 'graduation_result', $title, $body, null);
    }

    // ── SUBMISSION (Mentor) ──────────────────────────────────────────────────

    /**
     * Notify mentor that a student has submitted a task assignment.
     */
    public static function taskSubmitted(string $mentorId, string $studentName, string $taskTitle, string $submissionId): void
    {
        self::create(
            $mentorId,
            'task_submitted',
            '📥 Tugas Masuk',
            "{$studentName} telah mengumpulkan tugas \"{$taskTitle}\". Silakan periksa dan berikan feedback.",
            $submissionId
        );
    }

    /**
     * Broadcast task-submitted to ALL mentors of that beasiswa.
     * @param string $beasiswaName  Beasiswa name (paket_beasiswa from the Task model)
     */
    public static function broadcastTaskSubmittedToMentors(string $beasiswaName, string $studentName, string $taskTitle, string $submissionId): void
    {
        foreach (self::getMentorsByBeasiswaName($beasiswaName) as $mentor) {
            self::taskSubmitted((string) $mentor->_id, $studentName, $taskTitle, $submissionId);
        }
    }

    // ── TESTIMONIAL (Mentor) ─────────────────────────────────────────────────

    /**
     * Notify mentor that a student has left a testimonial.
     */
    public static function newTestimonial(string $mentorId, string $studentName, int $rating, string $content, string $testimonialId): void
    {
        $stars = str_repeat('⭐', $rating);
        self::create(
            $mentorId,
            'new_testimonial',
            "⭐ Testimoni Baru dari {$studentName}",
            "{$stars}\n\"{$content}\"",
            $testimonialId
        );
    }
}
