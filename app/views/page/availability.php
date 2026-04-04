
        <!-- Availability View -->
        <div id="availability-view" class="view-container">
            <div class="view-header">
                <div class="greeting">
                    <h1>Update availability</h1>
                    <p>Set your working hours and available time slots</p>
                </div>
            </div>

            <div class="availability-container">
                <!-- Left: Days Configuration -->
                <div class="availability-left">
                    <div class="days-config">
                        <div class="menu-title">Working days</div>
                        
                        <!-- Monday -->
                        <div class="day-config active" data-day="monday">
                            <div class="day-toggle" onclick="toggleDay('monday')">
                                <span>Monday</span>
                                <label class="switch">
                                    <input type="checkbox" id="monday-toggle" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="day-settings" id="monday-settings">
                                <div class="time-inputs">
                                    <div class="time-input-group">
                                        <label>Start Time</label>
                                        <input type="time" id="monday-start" value="09:00" onchange="updateSlots()">
                                    </div>
                                    <div class="time-input-group">
                                        <label>End Time</label>
                                        <input type="time" id="monday-end" value="17:00" onchange="updateSlots()">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tuesday -->
                        <div class="day-config active" data-day="tuesday">
                            <div class="day-toggle" onclick="toggleDay('tuesday')">
                                <span>Tuesday</span>
                                <label class="switch">
                                    <input type="checkbox" id="tuesday-toggle" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="day-settings" id="tuesday-settings">
                                <div class="time-inputs">
                                    <div class="time-input-group">
                                        <label>Start Time</label>
                                        <input type="time" id="tuesday-start" value="09:00" onchange="updateSlots()">
                                    </div>
                                    <div class="time-input-group">
                                        <label>End Time</label>
                                        <input type="time" id="tuesday-end" value="17:00" onchange="updateSlots()">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Wednesday -->
                        <div class="day-config active" data-day="wednesday">
                            <div class="day-toggle" onclick="toggleDay('wednesday')">
                                <span>Wednesday</span>
                                <label class="switch">
                                    <input type="checkbox" id="wednesday-toggle" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="day-settings" id="wednesday-settings">
                                <div class="time-inputs">
                                    <div class="time-input-group">
                                        <label>Start Time</label>
                                        <input type="time" id="wednesday-start" value="09:00" onchange="updateSlots()">
                                    </div>
                                    <div class="time-input-group">
                                        <label>End Time</label>
                                        <input type="time" id="wednesday-end" value="17:00" onchange="updateSlots()">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thursday -->
                        <div class="day-config" data-day="thursday">
                            <div class="day-toggle" onclick="toggleDay('thursday')">
                                <span>Thursday</span>
                                <label class="switch">
                                    <input type="checkbox" id="thursday-toggle">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="day-settings hidden" id="thursday-settings">
                                <div class="time-inputs">
                                    <div class="time-input-group">
                                        <label>Start Time</label>
                                        <input type="time" id="thursday-start" value="09:00" onchange="updateSlots()">
                                    </div>
                                    <div class="time-input-group">
                                        <label>End Time</label>
                                        <input type="time" id="thursday-end" value="17:00" onchange="updateSlots()">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Friday -->
                        <div class="day-config active" data-day="friday">
                            <div class="day-toggle" onclick="toggleDay('friday')">
                                <span>Friday</span>
                                <label class="switch">
                                    <input type="checkbox" id="friday-toggle" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="day-settings" id="friday-settings">
                                <div class="time-inputs">
                                    <div class="time-input-group">
                                        <label>Start Time</label>
                                        <input type="time" id="friday-start" value="09:00" onchange="updateSlots()">
                                    </div>
                                    <div class="time-input-group">
                                        <label>End Time</label>
                                        <input type="time" id="friday-end" value="17:00" onchange="updateSlots()">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Saturday -->
                        <div class="day-config" data-day="saturday">
                            <div class="day-toggle" onclick="toggleDay('saturday')">
                                <span>Saturday</span>
                                <label class="switch">
                                    <input type="checkbox" id="saturday-toggle">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="day-settings hidden" id="saturday-settings">
                                <div class="time-inputs">
                                    <div class="time-input-group">
                                        <label>Start Time</label>
                                        <input type="time" id="saturday-start" value="09:00" onchange="updateSlots()">
                                    </div>
                                    <div class="time-input-group">
                                        <label>End Time</label>
                                        <input type="time" id="saturday-end" value="12:00" onchange="updateSlots()">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sunday -->
                        <div class="day-config" data-day="sunday">
                            <div class="day-toggle" onclick="toggleDay('sunday')">
                                <span>Sunday</span>
                                <label class="switch">
                                    <input type="checkbox" id="sunday-toggle">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="day-settings hidden" id="sunday-settings">
                                <div class="time-inputs">
                                    <div class="time-input-group">
                                        <label>Start Time</label>
                                        <input type="time" id="sunday-start" value="09:00" onchange="updateSlots()">
                                    </div>
                                    <div class="time-input-group">
                                        <label>End Time</label>
                                        <input type="time" id="sunday-end" value="12:00" onchange="updateSlots()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Break Duration -->
                    <div class="break-config">
                        <div class="menu-title">Break between appointments</div>
                        <div class="break-options">
                            <label class="break-option">
                                <input type="radio" name="break-duration" value="5" checked onchange="updateSlots()">
                                <span>5 min</span>
                            </label>
                            <label class="break-option">
                                <input type="radio" name="break-duration" value="7" onchange="updateSlots()">
                                <span>7 min</span>
                            </label>
                            <label class="break-option">
                                <input type="radio" name="break-duration" value="10" onchange="updateSlots()">
                                <span>10 min</span>
                            </label>
                        </div>
                    </div>
                    
                    <button class="btn-save" onclick="saveAvailability()">Save Availability</button>
                </div>

                <!-- Right: Slot Preview -->
                <div class="availability-right">
                    <div class="slot-preview-header">
                        <h3>Slot Preview</h3>
                        <p>Live preview of your available time slots</p>
                    </div>
                    <div class="preview-day-selector">
                        <button class="preview-day-btn active" data-preview-day="monday" onclick="setPreviewDay('monday')">Mon</button>
                        <button class="preview-day-btn" data-preview-day="tuesday" onclick="setPreviewDay('tuesday')">Tue</button>
                        <button class="preview-day-btn" data-preview-day="wednesday" onclick="setPreviewDay('wednesday')">Wed</button>
                        <button class="preview-day-btn" data-preview-day="thursday" onclick="setPreviewDay('thursday')">Thu</button>
                        <button class="preview-day-btn" data-preview-day="friday" onclick="setPreviewDay('friday')">Fri</button>
                        <button class="preview-day-btn" data-preview-day="saturday" onclick="setPreviewDay('saturday')">Sat</button>
                        <button class="preview-day-btn" data-preview-day="sunday" onclick="setPreviewDay('sunday')">Sun</button>
                    </div>
                    <div class="slots-preview-grid" id="slots-preview-grid">
                        <!-- Generated by JS -->
                    </div>
                </div>
            </div>
        </div>