CREATE TABLE IF NOT EXISTS "points" (
    "timestamp" int, -- unix timestamp
    "latitude" real,
    "longitude" real,
    "altitude" int, -- meters
    "speed" int, -- meters per second
    "motion" text, -- motion[] -> sort abc -> join with hyphens
    "horizontal_accuracy" int, -- meters
    "vertical_accuracy" int, -- meters
    "wifi" text,
    "battery_state" text,
    "battery_level" real -- between 0 and 1
);