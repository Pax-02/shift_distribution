// Mirrors the PHP string-backed enums in app/Enums/ — kept as a single
// source of truth for status/option values shared across the type files.

export type ShiftStatus = 'pending' | 'allocated' | 'confirmed' | 'completed' | 'cancelled';

export type AssignmentStatus = 'proposed' | 'confirmed' | 'declined' | 'cancelled';

export type GenderRequirement = 'any' | 'male' | 'female';

export type Gender = 'male' | 'female' | 'other' | 'prefer_not_to_say';

export type JourneyPreference = 'short' | 'long' | 'any';

export type MessageChannel = 'email' | 'sms' | 'whatsapp' | 'manual';

export type ParseStatus = 'pending' | 'parsed' | 'validated' | 'failed' | 'rejected';
